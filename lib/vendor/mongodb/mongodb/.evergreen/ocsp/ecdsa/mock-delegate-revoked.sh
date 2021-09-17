#!/usr/bin/env sh
python3 ../ocsp_mock.py \
  --ca_file ca.pem \
  --ocsp_responder_cert ocsp-responder.crt \
  --ocsp_responder_key ocsp-responder.key \
   -p 8100 \
   -v \
   --fault revoked
