# Install

GCloud Encrypt:
`gcloud kms encrypt --ciphertext-file=./storage/credentials/google/smiling-timing-148416-9bf14e9b3047.json.enc --plaintext-file=./storage/credentials/google/smiling-timing-148416-9bf14e9b3047.json --location=global --keyring=testing-lesson --key=service-account
`

GCloud Decrypt: `gcloud kms decrypt --ciphertext-file=./storage/credentials/google/smiling-timing-148416-9bf14e9b3047.json.enc --plaintext-file=./storage/credentials/google/smiling-timing-148416-9bf14e9b3047.json --location=global --keyring=testing-lesson --key=service-account
`
