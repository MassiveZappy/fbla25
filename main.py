import json
import os
import hashlib

class jsonDiskVariable:
    def __init__(self, name):
        self.name = name
        self.file_path = f"{name}.json"

        # Check if the file exists on disk
        if os.path.exists(self.file_path):
            # Load the existing dictionary from the file
            with open(self.file_path, 'r') as file:
                self.variable = json.load(file)
        else:
            # Create a new dictionary if the file does not exist
            self.variable = {}
            self._save_to_disk()

    def _save_to_disk(self):
        """Save the current state of the dictionary to disk."""
        with open(self.file_path, 'w') as file:
            json.dump(self.variable, file, indent=4)

    def get(self, key, default=None):
        """Get the value for a key from the dictionary."""
        return self.variable.get(key, default)

    def set(self, key, value):
        """Set the value for a key in the dictionary and save to disk."""
        self.variable[key] = value
        self._save_to_disk()

    def delete(self, key):
        """Delete a key from the dictionary and save to disk."""
        if key in self.variable:
            del self.variable[key]
            self._save_to_disk()

    def clear(self):
        """Clear the dictionary and save to disk."""
        self.variable.clear()
        self._save_to_disk()

    def __repr__(self):
        return f"jsonDiskVariable(name={self.name}, variable={self.variable})"

class AccountSystem:
    def __init__(self, storage_name='accounts'):
        # Use jsonDiskVariable to store account data
        self.accounts = jsonDiskVariable(storage_name)

    def _hash_password(self, password, salt=None):
        """Hash a password with an optional salt."""
        if salt is None:
            salt = os.urandom(16)  # Generate a new 16-byte salt
        else:
            salt = bytes.fromhex(salt)
        # Use PBKDF2 HMAC SHA256 for hashing
        key = hashlib.pbkdf2_hmac(
            'sha256',  # The hash digest algorithm for HMAC
            password.encode('utf-8'),  # Convert the password to bytes
            salt,  # Provide the salt
            100_000  # It is recommended to use at least 100,000 iterations of SHA-256
        )
        return salt.hex(), key.hex()

    def create_account(self, username, password):
        """Create a new account with the given username and password."""
        if username in self.accounts.variable:
            print("Username already exists.")
            return False
        salt, hashed_pw = self._hash_password(password)
        self.accounts.set(username, {'salt': salt, 'password': hashed_pw})
        print(f"Account '{username}' created successfully.")
        return True

    def authenticate(self, username, password):
        """Authenticate a user with a username and password."""
        user = self.accounts.get(username)
        if not user:
            print("Username does not exist.")
            return False
        salt = user['salt']
        _, hashed_pw = self._hash_password(password, salt)
        if hashed_pw == user['password']:
            print("Authentication successful.")
            return True
        else:
            print("Authentication failed.")
            return False

    def change_password(self, username, old_password, new_password):
        """Change the password for a user after verifying the old password."""
        if not self.authenticate(username, old_password):
            print("Old password is incorrect.")
            return False
        salt, hashed_pw = self._hash_password(new_password)
        self.accounts.set(username, {'salt': salt, 'password': hashed_pw})
        print("Password changed successfully.")
        return True

    def delete_account(self, username, password):
        """Delete a user account after verifying the password."""
        if not self.authenticate(username, password):
            print("Authentication failed. Cannot delete account.")
            return False
        self.accounts.delete(username)
        print(f"Account '{username}' deleted successfully.")
        return True
