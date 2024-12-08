from flask import Flask, request, jsonify
from main import accSystem

app = Flask(__name__)
accSys = accSystem()

@app.route('/createAccount', methods=['POST'])
def createAccount():
    data = request.json
    name = data.get('name')
    email = data.get('email')
    password = data.get('password')
    if not name or not email or not password:
        return jsonify({'success': False, 'message': 'Name, email, and password are required.'}), 400
    result = accSys.createAccount(name, email, password)
    if result:
        return jsonify({'success': True, 'message': f"Account for '{email}' created successfully."}), 200
    else:
        return jsonify({'success': False, 'message': 'Email already exists.'}), 400

@app.route('/authenticate', methods=['POST'])
def authenticate():
    data = request.json
    email = data.get('email')
    password = data.get('password')
    if not email or not password:
        return jsonify({'success': False, 'message': 'Email and password are required.'}), 400
    result = accSys.authenticate(email, password)
    if result:
        return jsonify({'success': True, 'message': 'Authentication successful.'}), 200
    else:
        return jsonify({'success': False, 'message': 'Authentication failed.'}), 401

@app.route('/createSession', methods=['POST'])
def createSession():
    data = request.json
    email = data.get('email')
    if not email:
        return jsonify({'success': False, 'message': 'Email is required.'}), 400
    sessionToken = accSys.createSession(email)
    if sessionToken:
        return jsonify({'success': True, 'sessionToken': sessionToken}), 200
    else:
        return jsonify({'success': False, 'message': 'Email does not exist.'}), 400

@app.route('/validateSession', methods=['POST'])
def validateSession():
    data = request.json
    email = data.get('email')
    sessionToken = data.get('sessionToken')
    if not email or not sessionToken:
        return jsonify({'success': False, 'message': 'Email and sessionToken are required.'}), 400
    isValid = accSys.validateSession(email, sessionToken)
    return jsonify({'success': True, 'isValid': isValid}), 200

@app.route('/createTransactionalList', methods=['POST'])
def createTransactionalList():
    data = request.json
    email = data.get('email')
    name = data.get('name')
    description = data.get('description')
    if not email or not name or not description:
        return jsonify({'success': False, 'message': 'Email, name, and description are required.'}), 400
    result = accSys.createTransactionalList(email, name, description)
    if result:
        return jsonify({'success': True, 'message': f"Transactional list '{name}' created successfully."}), 200
    else:
        return jsonify({'success': False, 'message': 'Failed to create transactional list.'}), 400

@app.route('/addUserToTransactionalList', methods=['POST'])
def addUserToTransactionalList():
    data = request.json
    email = data.get('email')  # Admin email
    tlUUID = data.get('tlUUID')
    memberEmail = data.get('memberEmail')
    if not email or not tlUUID or not memberEmail:
        return jsonify({'success': False, 'message': 'Email, tlUUID, and memberEmail are required.'}), 400
    result = accSys.addUserToTransactionalList(email, tlUUID, memberEmail)
    if result:
        return jsonify({'success': True, 'message': f"User '{memberEmail}' invited to transactional list."}), 200
    else:
        return jsonify({'success': False, 'message': 'Failed to invite user to transactional list.'}), 400

@app.route('/acceptInvitation', methods=['POST'])
def acceptInvitation():
    data = request.json
    memberEmail = data.get('memberEmail')
    tlUUID = data.get('tlUUID')
    if not memberEmail or not tlUUID:
        return jsonify({'success': False, 'message': 'Member email and tlUUID are required.'}), 400
    result = accSys.acceptInvitation(memberEmail, tlUUID)
    if result:
        return jsonify({'success': True, 'message': f"User '{memberEmail}' joined the transactional list."}), 200
    else:
        return jsonify({'success': False, 'message': 'Failed to accept invitation.'}), 400

@app.route('/addEvent', methods=['POST'])
def addEvent():
    data = request.json
    email = data.get('email')
    tlUUID = data.get('tlUUID')
    name = data.get('name')
    description = data.get('description')
    time = data.get('time')  # Should be a string in ISO format
    scheduled = data.get('scheduled', False)
    amount = data.get('amount')
    byUserEmail = data.get('byUserEmail')
    forUserEmail = data.get('forUserEmail')
    if not all([email, tlUUID, name, description, time, amount, byUserEmail, forUserEmail]):
        return jsonify({'success': False, 'message': 'Missing required fields.'}), 400
    result = accSys.addEvent(
        email=email,
        tlUUID=tlUUID,
        name=name,
        description=description,
        time=time,
        scheduled=scheduled,
        amount=amount,
        byUserEmail=byUserEmail,
        forUserEmail=forUserEmail
    )
    if result:
        return jsonify({'success': True, 'message': f"Event '{name}' added successfully."}), 200
    else:
        return jsonify({'success': False, 'message': 'Failed to add event.'}), 400

@app.route('/markEventAsPaid', methods=['POST'])
def markEventAsPaid():
    data = request.json
    email = data.get('email')
    tlUUID = data.get('tlUUID')
    eventUUID = data.get('eventUUID')
    if not email or not tlUUID or not eventUUID:
        return jsonify({'success': False, 'message': 'Email, tlUUID, and eventUUID are required.'}), 400
    result = accSys.markEventAsPaid(email, tlUUID, eventUUID)
    if result:
        return jsonify({'success': True, 'message': 'Event marked as paid.'}), 200
    else:
        return jsonify({'success': False, 'message': 'Failed to mark event as paid.'}), 400

@app.route('/getNotifications', methods=['GET'])
def getNotifications():
    email = request.args.get('email')
    if not email:
        return jsonify({'success': False, 'message': 'Email is required.'}), 400
    notifications = accSys.getNotifications(email)
    return jsonify({'success': True, 'notifications': notifications}), 200

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5050)
