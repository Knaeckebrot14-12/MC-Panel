# Small helper image that applies panel updates. It has the Docker CLI (with the compose
# and buildx plugins) and git; the actual logic lives in updater.sh, which is mounted from
# the install directory so that updates to the script apply without rebuilding this image.
FROM docker:cli
RUN apk add --no-cache bash git curl \
    && git config --system --add safe.directory '*'
