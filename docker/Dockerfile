# fabric-app + PHP (Apache). Build context must be the repo root (see compose.yml).
FROM php:8.4-apache-bookworm

ENV DEBIAN_FRONTEND=noninteractive \
    FABRIC_APP_BIN=/opt/venv/bin/fabric-app \
    PATH="/opt/venv/bin:/usr/local/bin:${PATH}"

RUN apt-get update && apt-get install -y --no-install-recommends \
    fonts-noto-cjk \
    fontconfig \
    python3 \
    python3-pip \
    python3-venv \
    ca-certificates \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && fc-cache -f

COPY docker/install-google-fonts.sh /tmp/install-google-fonts.sh
RUN chmod +x /tmp/install-google-fonts.sh \
    && /tmp/install-google-fonts.sh \
    && rm /tmp/install-google-fonts.sh

# Prebuilt CLI tarball is x86_64-only on current linebender releases — build as amd64
# (Compose sets platform: linux/amd64 so Apple Silicon hosts still work via emulation).
ARG TARGETARCH
ARG RESVG_VERSION=v0.47.0
RUN set -eux; \
    if [ "$TARGETARCH" != "amd64" ]; then \
      echo "This Dockerfile installs resvg-linux-x86_64; use Compose platform: linux/amd64." >&2; \
      exit 1; \
    fi; \
    curl -fsSL "https://github.com/linebender/resvg/releases/download/${RESVG_VERSION}/resvg-linux-x86_64.tar.gz" \
      | tar -xz -C /usr/local/bin resvg; \
    chmod +x /usr/local/bin/resvg; \
    resvg --version

WORKDIR /opt/fabric-build
COPY pyproject.toml README.md LICENSE ./
COPY src ./src
RUN python3 -m venv /opt/venv \
    && /opt/venv/bin/python -m pip install -U pip \
    && /opt/venv/bin/pip install --no-cache-dir .

WORKDIR /var/www/html
COPY web/ ./

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
