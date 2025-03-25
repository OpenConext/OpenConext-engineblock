#!/usr/bin/env python
import json
import logging
import secrets

from flask import Flask, Response, request, render_template

logging.getLogger().setLevel(logging.DEBUG)
logging.getLogger('flask_pyoidc').setLevel(logging.ERROR)
logging.getLogger('oic').setLevel(logging.ERROR)
logging.getLogger('jwkest').setLevel(logging.ERROR)
logging.getLogger('urllib3').setLevel(logging.ERROR)
logging.getLogger('werkzeug').setLevel(logging.ERROR)

app = Flask(__name__, template_folder='templates', static_folder='static')

nonces = {}


def debug(request):
    for header in request.headers:
        logging.debug(header)
    logging.debug(f'request.args: {request.args}')
    logging.debug(f'request.data: {request.data}')
    logging.debug(f'request.form: {request.form}')
    logging.debug(f'request.json: {request.json}')


@app.route('/', defaults={'path': ''})
@app.route('/<path:path>')
def catch_all(path):
    logging.debug(f'-> {path}')
    debug(request)
    response = Response(status=200)
    return response


@app.route('/api/users/authz_eb', methods=['POST'])
def authz():
    logging.debug('-> /api/users/authz_eb')
    debug(request)

    uid = request.json.get('user_id')
    continue_url = request.json.get('continue_url')
    service_entity_id = request.json.get('service_id')
    issuer_id = request.json.get('issuer_id')

    nonce = secrets.token_urlsafe()
    nonces[nonce] = (uid, continue_url, service_entity_id, issuer_id)

    response = Response(status=200)
    body = {
        # 'msg': 'interrupt',
        'msg': 'authorized',
        'nonce': nonce,
        'attributes': {
            'urn:mace:dir:attribute-def:eduPersonEntitlement': [
                uid,
                nonce,
                'urn:foobar'
            ],
            'urn:mace:dir:attribute-def:uid': ['SBS-uid'],
            'urn:oid:1.3.6.1.4.1.24552.500.1.1.1.13': ['someKey'],
        }
    }

    logging.debug(f'<- {body}')
    response.data = json.dumps(body)

    return response


@app.route('/api/users/interrupt', methods=['GET'])
def interrupt():
    logging.debug('-> /api/users/interrupt')
    nonce = request.args.get('nonce')
    (uid, continue_url, service_entity_id, issuer_id) = nonces.get(nonce, ('unknown', '/', '/', ''))
    response = render_template('interrupt.j2', uid=uid,
                               service_entity_id=service_entity_id, issuer_id=issuer_id, url=continue_url)

    return response


@app.route('/api/users/attributes', methods=['POST'])
def attributes():
    logging.debug('-> /api/users/attributes')
    debug(request)

    nonce = request.json.get('nonce')
    (uid, _, _, _) = nonces.pop(nonce)

    response = Response(status=200)
    body = {
        'attributes': {
            'urn:mace:dir:attribute-def:eduPersonEntitlement': [
                uid,
                nonce,
                'urn:foobar',
            ]
        }
    }

    logging.debug(f'<- {body}')
    response.data = json.dumps(body)

    return response


if __name__ == "__main__":
    app.run(host='0.0.0.0', port=12345, debug=True)
