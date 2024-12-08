import json
import os
import hashlib
import uuid
from datetime import datetime, timedelta

class jsonDiskVariable:
    """Json dctionary that is saved/loaded to/from disk."""
    def __init__(self, name):
        """Define what needs definitions, as in the name of the file and class."""
        self.name = name
        self.file_path = f"{name}.json"

        # Create file or laod it
        if os.path.exists(self.file_path):
            with open(self.file_path, 'r') as file:
                try:
                    self.variable = json.load(file)
                except json.JSONDecodeError:
                    self.variable = {}
        else:
            self.variable = {}
            self.saveToDisk()

    def saveToDisk(self):
        """Save the current dict to disk"""
        with open(self.file_path, 'w') as file:
            json.dump(self.variable, file, indent=4)

    def get(self, key, default=None):
        """Get the value for a key from the dict"""
        return self.variable.get(key, default)

    def set(self, key, value):
        """Set the value for a key in the dict"""
        self.variable[key] = value
        self.saveToDisk()

    def delete(self, key):
        """Delete a key from the dict"""
        if key in self.variable:
            del self.variable[key]
            self.saveToDisk()

    def clear(self):
        """Clear the dict"""
        self.variable.clear()
        self.saveToDisk()

    def __repr__(self):
        return f"jsonDiskVariable(name={self.name}, variable={self.variable})"

class accSystem:
    def __init__(self):
        self.data = jsonDiskVariable('accountData')

        # Creates maps if they don't exist
        if 'users' not in self.data.variable:
            self.data.variable['users'] = {}
        if 'transactionalLists' not in self.data.variable:
            self.data.variable['transactionalLists'] = {}
        if 'events' not in self.data.variable:
            self.data.variable['events'] = {}
        self.data.saveToDisk()

    def hashPassword(self, password, salt=None):
        """Hash a password to encypt and hide its plaintext form (salt is like offset in ceaser cipher)"""
        if salt is None:
            salt = os.urandom(16)
        else:
            salt = bytes.fromhex(salt)
        key = hashlib.pbkdf2_hmac('sha256', password.encode('utf-8'), salt, 100_000)
        return salt.hex(), key.hex()

    def createAccount(self, name, email, password):
        """create an account"""
        if email in self.data.variable['users']:
            print("Email already exists.")
            return False
        salt, hashedPassword = self.hashPassword(password)
        userUUID = str(uuid.uuid4())
        userData = {
            'Name': name,
            'Email': email,
            'EncryptedPassword': {'salt': salt, 'password': hashedPassword},
            'TransactionalList': [],
            'SessionToken': '',
            'SessionExpiry': '',
            'UUID': userUUID,
            'Notifications': []
        }
        self.data.variable['users'][email] = userData
        self.data.saveToDisk()
        print(f"Account for '{email}' created successfully.")

        return True

    def authenticate(self, email, password):
        """Authenticate a user w/ an email"""
        user = self.data.variable['users'].get(email)
        if not user:
            print("Email does not exist.")
            return False
        if self.hashPassword(password, user['EncryptedPassword']['salt'])[1] == user['EncryptedPassword']['password']:
            print("Authentication successful.")
            return True
        else:
            print("Authentication failed.")
            return False

    def changePassword(self, email, oldPassword, newPassword):
        """Change the password for a user (requires old password)"""
        if not self.authenticate(email, oldPassword):
            print("Old password is incorrect.")
            return False
        user = self.data.variable['users'][email]
        salt, hashedPassword = self.hashPassword(newPassword)
        user['EncryptedPassword'] = {'salt': salt, 'password': hashedPassword}
        self.data.saveToDisk()
        print("Password changed successfully.")
        return True

    def deleteAccount(self, email, password):
        """Delete a user account after verifying the password."""
        if not self.authenticate(email, password):
            print("Authentication failed. Cannot delete account.")
            return False
        user = self.data.variable['users'][email]
        for tlUUID in user['TransactionalList']:
            self.deleteTransactionalList(user['UUID'], tlUUID)
        del self.data.variable['users'][email]
        self.data.saveToDisk()
        print(f"Account for '{email}' deleted successfully.")
        return True

    def createSession(self, email):
        """Create a session token and expiry for the user."""
        user = self.data.variable['users'].get(email)
        if not user:
            print("Email does not exist.")
            return None
        sessionToken = str(uuid.uuid4())
        sessionExpiry = (datetime.now() + timedelta(hours=2)).isoformat()
        user['SessionToken'] = sessionToken
        user['SessionExpiry'] = sessionExpiry
        self.data.saveToDisk()
        return sessionToken

    def validateSession(self, email, sessionToken):
        """Validate the session token."""
        user = self.data.variable['users'].get(email)
        if not user:
            return False
        if user['SessionToken'] != sessionToken:
            return False
        if datetime.fromisoformat(user['SessionExpiry']) < datetime.now():
            return False
        return True

    def validateSessionWithoutEmail(self, sessionToken):
        for user in self.data.variable['users']:
            if user['SessionToken'] == sessionToken:
                if datetime.fromisoformat(self.data.variable['users'][user]['SessionExpiry']) < datetime.now():
                    return False
                return True


    def createTransactionalList(self, email, name, description):
        """Create a new TransactionalList (list of transactions that individual users can be added to)."""
        user = self.data.variable['users'].get(email)
        if not user:
            print(f"Email '{email}' does not exist.")
            return False
        userUUID = user['UUID']
        tlUUID = str(uuid.uuid4())
        transactionalList = {
            'Name': name,
            'Description': description,
            'Events': [],
            'Admins': [userUUID],
            'Members': [userUUID],
            'InvitedMembers': []
        }
        self.data.variable['transactionalLists'][tlUUID] = transactionalList
        user['TransactionalList'].append(tlUUID)
        self.data.saveToDisk()
        print(f"Transactional list '{name}' created successfully.")
        return True

    def deleteTransactionalList(self, userUUID, tlUUID):
        """delete a TransactionalList"""
        transactionalList = self.data.variable['transactionalLists'].get(tlUUID)
        if not transactionalList:
            print("Transactional list does not exist.")
            return False
        # Permissions check
        if userUUID not in transactionalList['Admins']:
            print("User is not an admin of the transactional list.")
            return False
        # Remove all events
        for eventUUID in transactionalList['Events']:
            if eventUUID in self.data.variable['events']:
                del self.data.variable['events'][eventUUID]

        del self.data.variable['transactionalLists'][tlUUID]
        # Remve the list from all users TransactionalList arrays
        for memberEmail in self.data.variable['users']:
            member = self.data.variable['users'][memberEmail]
            if tlUUID in member['TransactionalList']:
                member['TransactionalList'].remove(tlUUID)
        self.data.saveToDisk()
        print(f"Transactional list '{transactionalList['Name']}' deleted successfully.")
        return True

    def addUserToTransactionalList(self, email, tlUUID, memberEmail):
        """Invite a user to a list (responsibilities!)"""
        user = self.data.variable['users'].get(email)
        member = self.data.variable['users'].get(memberEmail)
        if not user or not member:
            print("Email does not exist.")
            return False
        userUUID = user['UUID']
        memberUUID = member['UUID']
        transactionalList = self.data.variable['transactionalLists'].get(tlUUID)
        if not transactionalList:
            print("Transactional list does not exist.")
            return False
        # Perms
        if userUUID not in transactionalList['Admins']:
            print("User is not an admin of the transactional list.")
            return False
        # Invite the user
        if memberUUID not in transactionalList['Members']:
            transactionalList['InvitedMembers'].append(memberUUID)
            self.data.saveToDisk()
            print(f"User '{memberEmail}' invited to transactional list '{transactionalList['Name']}'.")
            return True
        else:
            print(f"User '{memberEmail}' is already a member of the transactional list.")
            return False

    def acceptInvitation(self, memberEmail, tlUUID):
        """User accepts an invitation to a TransactionalList."""
        member = self.data.variable['users'].get(memberEmail)
        if not member:
            print(f"Email '{memberEmail}' does not exist.")
            return False
        memberUUID = member['UUID']
        transactionalList = self.data.variable['transactionalLists'].get(tlUUID)
        if not transactionalList:
            print("Transactional list does not exist.")
            return False
        # Check if member is in InvitedMembers, then move to Members
        if memberUUID in transactionalList['InvitedMembers']:
            transactionalList['InvitedMembers'].remove(memberUUID)
            transactionalList['Members'].append(memberUUID)
            member['TransactionalList'].append(tlUUID)
            self.data.saveToDisk()
            print(f"User '{memberEmail}' joined the transactional list '{transactionalList['Name']}'.")
            return True
        else:
            print("Invitation does not exist.")
            return False

    def addEvent(self, email, tlUUID, name, description, time, scheduled, amount, byUserEmail, forUserEmail):
        """Add an event to a TransactionalList."""
        user = self.data.variable['users'].get(email)
        if not user:
            print(f"Email '{email}' does not exist.")
            return False
        userUUID = user['UUID']
        transactionalList = self.data.variable['transactionalLists'].get(tlUUID)
        if not transactionalList:
            print("Transactional list does not exist.")
            return False
        # Check if the user is a member
        if userUUID not in transactionalList['Members']:
            print("User is not a member of the transactional list.")
            return False
        byUser = self.data.variable['users'].get(byUserEmail)
        forUser = self.data.variable['users'].get(forUserEmail)
        if not byUser or not forUser:
            print("One of the users does not exist.")
            return False
        byUserUUID = byUser['UUID']
        forUserUUID = forUser['UUID']
        if byUserUUID not in transactionalList['Members'] or forUserUUID not in transactionalList['Members']:
            print("Users must be members of the transactional list.")
            return False
        eventUUID = str(uuid.uuid4())
        eventData = {
            'Name': name,
            'Description': description,
            'Time': time,
            'Scheduled': scheduled,
            'Amount': amount,
            'ByUser': byUserUUID,
            'ForUser': forUserUUID,
            'Paid': False
        }
        self.data.variable['events'][eventUUID] = eventData
        transactionalList['Events'].append(eventUUID)
        self.data.saveToDisk()
        print(f"Event '{name}' added to transactional list '{transactionalList['Name']}' successfully.")
        return True

    def markEventAsPaid(self, email, tlUUID, eventUUID):
        """mark an event as paid"""
        user = self.data.variable['users'].get(email)
        if not user:
            print("Email does not exist.")
            return False
        userUUID = user['UUID']
        transactionalList = self.data.variable['transactionalLists'].get(tlUUID)
        if not transactionalList:
            print("Transactional list does not exist.")
            return False
        if userUUID not in transactionalList['Members']:
            print("User is not a member of the transactional list.")
            return False
        eventData = self.data.variable['events'].get(eventUUID)
        if not eventData:
            print("Event does not exist.")
            return False
        # Only the 'ForUser' can mark the event as paid
        if eventData['ForUser'] != userUUID:
            print("Only the recipient can mark the event as paid.")
            return False
        eventData['Paid'] = True
        self.data.saveToDisk()
        print(f"Event '{eventData['Name']}' marked as paid.")
        return True

    def addNotification(self, email, notificationData):
        """add an notification to a user"""
        user = self.data.variable['users'].get(email)
        if not user:
            print("User does not exist.")
            return False
        user['Notifications'].append(notificationData)
        self.data.saveToDisk()
        print("Notification added to user.")
        return True

    def getNotifications(self, email):
        """Retrieve notifications for a user"""
        user = self.data.variable['users'].get(email)
        if not user:
            print("Email does not exist.")
            return []
        return user.get('Notifications', [])

    def getUserEmailByUUID(self, uuid):
           for email, user in self.data.variable['users'].items():
               if user['UUID'] == uuid:
                   return email
           return None

    def getUserNameByUUID(self, uuid):
           for email, user in self.data.variable['users'].items():
                if user['UUID'] == uuid:
                     return user['Name']
           return None

if __name__ == "__main__":
    accSys = accSystem()

    accSys.createAccount('Alice Smith', 'alice@example.com', 'password123')
    accSys.createAccount('Bob Jones', 'bob@example.com', 'securepass456')

    accSys.authenticate('alice@example.com', 'password123')

    sessionToken = accSys.createSession('alice@example.com')

    is_valid = accSys.validateSession('alice@example.com', sessionToken)
    print(f"Session valid: {is_valid}")

    accSys.createTransactionalList('alice@example.com', 'Name1!', 'Desc2@')

    alice = accSys.data.variable['users']['alice@example.com']
    tlUUID = alice['TransactionalList'][0]
    accSys.addUserToTransactionalList('alice@example.com', tlUUID, 'bob@example.com')

    accSys.acceptInvitation('bob@example.com', tlUUID)

    accSys.addEvent(
        email='alice@example.com',
        tlUUID=tlUUID,
        name='Grocery Shopping',
        description='Weekly groceries',
        time=str(datetime.now()),
        scheduled=False,
        amount=150.00,
        byUserEmail='alice@example.com',
        forUserEmail='bob@example.com'
    )

    tlData = accSys.data.variable['transactionalLists'][tlUUID]
    eventUUID = tlData['Events'][0]
    accSys.markEventAsPaid('bob@example.com', tlUUID, eventUUID)

    notifications = accSys.getNotifications('alice@example.com')
    print("Notifications for Alice:", notifications)
