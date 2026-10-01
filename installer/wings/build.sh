#!/usr/bin/env bash
# Builds the Recoded Ptero Wings release files: the official pterodactyl/wings source at
# $UPSTREAM_TAG with recoded-ptero.patch applied, for amd64 and arm64, plus checksums.txt.
#
#   installer/wings/build.sh 1.0.0            -> files in installer/wings/dist/
#
# Then publish them as GitHub release "wings-v<version>" (the installer and the panel's
# "Update Wings" button download from there):
#   gh release upload wings-v1.0.0 installer/wings/dist/* --clobber
#
# Needs Docker and git; the Go toolchain runs in the golang image.
set -euo pipefail

VERSION="${1:?usage: build.sh <version, e.g. 1.0.0>}"
UPSTREAM_TAG="${UPSTREAM_TAG:-v1.13.3}"
GO_IMAGE="${GO_IMAGE:-golang:1.24}"
HERE="$(cd "$(dirname "$0")" && pwd)"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

git clone -q --depth 1 --branch "$UPSTREAM_TAG" https://github.com/pterodactyl/wings.git "$WORK/src"
git -C "$WORK/src" apply "$HERE/recoded-ptero.patch"

rm -rf "$HERE/dist"
mkdir -p "$HERE/dist"
docker run --rm -v "$WORK/src:/src" -v "$HERE/dist:/dist" -w /src -e CGO_ENABLED=0 "$GO_IMAGE" sh -c "
    for arch in amd64 arm64; do
        GOOS=linux GOARCH=\$arch go build -trimpath \
            -ldflags '-s -w -X github.com/pterodactyl/wings/system.Version=$VERSION' \
            -o /dist/wings_linux_\$arch . || exit 1
    done
"
cp "$WORK/src/LICENSE" "$HERE/dist/LICENSE-wings"
(cd "$HERE/dist" && sha256sum wings_linux_amd64 wings_linux_arm64 > checksums.txt)

echo "Built Wings $VERSION from pterodactyl/wings $UPSTREAM_TAG:"
cat "$HERE/dist/checksums.txt"
