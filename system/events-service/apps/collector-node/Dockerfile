FROM node:20-alpine AS builder

WORKDIR /app

# 1) Copy manifests (better caching)
COPY packages/contract/node/package.json packages/contract/node/package-lock.json* ./packages/contract/node/
COPY apps/collector-node/package.json apps/collector-node/package-lock.json* ./apps/collector-node/

# 2) Install and build contract (needed at runtime via file: dependency)
WORKDIR /app/packages/contract/node
RUN npm install
COPY packages/contract/node/tsconfig.json ./tsconfig.json
COPY packages/contract/node/src ./src
COPY packages/contract/tests ./../tests
RUN npm run build

# 3) Install and build collector
WORKDIR /app/apps/collector-node
RUN npm install
COPY apps/collector-node/tsconfig.json ./tsconfig.json
COPY apps/collector-node/src ./src
RUN npm run build

# 4) Prepare production node_modules (avoid npm in runner)
RUN npm prune --omit=dev

FROM node:20-alpine AS runner

WORKDIR /app
ENV NODE_ENV=production

# Keep folder structure so file: dependency resolves (contract dist)
COPY --from=builder /app/packages/contract/node/package.json ./packages/contract/node/package.json
COPY --from=builder /app/packages/contract/node/dist ./packages/contract/node/dist

# Collector runtime: dist + prod node_modules from builder
COPY --from=builder /app/apps/collector-node/package.json ./apps/collector-node/package.json
COPY --from=builder /app/apps/collector-node/node_modules ./apps/collector-node/node_modules
WORKDIR /app/apps/collector-node

COPY --from=builder /app/apps/collector-node/dist ./dist

EXPOSE 8080
CMD ["node","dist/server.js"]

