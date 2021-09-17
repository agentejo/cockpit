#!/usr/bin/env bash
[ ! -f ecdsa-ca-ocsp.pem ] || mv ecdsa-ca-ocsp.pem ca.pem
[ ! -f ecdsa-ca-ocsp.crt ] || mv ecdsa-ca-ocsp.crt ca.crt
[ ! -f ecdsa-ca-ocsp.key ] ||  mv ecdsa-ca-ocsp.key ca.key
[ ! -f ecdsa-server-ocsp.pem ] || mv ecdsa-server-ocsp.pem server.pem
[ ! -f ecdsa-server-ocsp-mustStaple.pem ] || mv ecdsa-server-ocsp-mustStaple.pem server-mustStaple.pem
[ ! -f ecdsa-server-ocsp-singleEndpoint.pem ] || mv ecdsa-server-ocsp-singleEndpoint.pem server-singleEndpoint.pem
[ ! -f ecdsa-server-ocsp-mustStaple-singleEndpoint.pem ] || mv ecdsa-server-ocsp-mustStaple-singleEndpoint.pem server-mustStaple-singleEndpoint.pem
[ ! -f ecdsa-ocsp-responder.crt ] || mv ecdsa-ocsp-responder.crt ocsp-responder.crt
[ ! -f ecdsa-ocsp-responder.key ] || mv ecdsa-ocsp-responder.key ocsp-responder.key
