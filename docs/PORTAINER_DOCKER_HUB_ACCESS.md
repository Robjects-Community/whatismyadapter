# Portainer Docker Hub Access Guide

## Issue: "Remote Not Found" Error

When deploying to Portainer, you may encounter a "remote not found" error when trying to pull the image `garzarobmdocker/willowcms:pre-willowcms-beta`. This happens because the Docker Hub repository is private and Portainer doesn't have credentials to access it.

## Solution 1: Make Docker Hub Repository Public (Recommended for Open Source)

### Steps:

1. Go to https://hub.docker.com
2. Log in with your `garzarobmdocker` account
3. Navigate to your repository: https://hub.docker.com/r/garzarobmdocker/willowcms
4. Click on **Settings** tab
5. Under **Visibility**, change from **Private** to **Public**
6. Click **Save**

### Benefits:
- No credentials needed in Portainer
- Faster deployment
- Anyone can pull the image
- Good for open source projects

### Drawbacks:
- Image is publicly accessible
- Anyone can see your Dockerfile layers

---

## Solution 2: Add Docker Hub Credentials to Portainer (Recommended for Private Projects)

### Steps:

1. **In Portainer:**
   - Go to **Registries** in the left sidebar
   - Click **+ Add registry**
   - Select **DockerHub** as the provider

2. **Configure Registry:**
   ```
   Name: DockerHub-garzarobmdocker
   Registry URL: docker.io (leave default)
   Authentication: ON
   Username: garzarobmdocker
   Password: [Your Docker Hub password or access token]
   ```

3. **Click "Add registry"**

4. **When creating/updating your stack:**
   - Make sure the registry is selected in the deployment options
   - Or add this to your docker-compose-cloud.yml for each service using private images:
   ```yaml
   services:
     willowcms:
       image: garzarobmdocker/willowcms:pre-willowcms-beta
       # Add this if using private registry:
       # pull_policy: always
   ```

### Using Docker Hub Access Token (More Secure):

1. Go to https://hub.docker.com/settings/security
2. Click **New Access Token**
3. Give it a name: `portainer-access`
4. Set permissions: **Read, Write, Delete** (or just **Read** for pulling only)
5. Click **Generate**
6. Copy the token (you won't see it again!)
7. Use this token as the password in Portainer registry configuration

---

## Solution 3: Use GitHub Container Registry Instead

If you prefer to keep using GitHub Container Registry:

1. Update `docker-compose-cloud.yml`:
   ```yaml
   image: ${WILLOWCMS_IMAGE:-ghcr.io/robjects-community/whatismyadapter_cms:pre-willowcms-beta}
   ```

2. Make sure the GitHub Container Registry package is public:
   - Go to https://github.com/orgs/robjects-community/packages
   - Find your package
   - Click **Package settings**
   - Under **Danger Zone**, click **Change visibility**
   - Select **Public**

---

## Verification

After implementing either solution, test the pull:

### From Command Line:
```bash
# Test public access (no login):
docker pull garzarobmdocker/willowcms:pre-willowcms-beta

# Or test with credentials:
docker login
docker pull garzarobmdocker/willowcms:pre-willowcms-beta
```

### In Portainer:
1. Try deploying your stack again
2. Check the logs if it fails
3. Verify the image is being pulled successfully

---

## Current Status

- ✅ Image exists on Docker Hub: `garzarobmdocker/willowcms:pre-willowcms-beta`
- ✅ Image digest: `sha256:5d1ed392c8bd5c43be8fa03e9492a06af4bff50aa6372305d0a5ec75db404e4e`
- ❓ Repository visibility: Private (needs to be made public or credentials added)

---

## Recommended Approach

For WillowCMS (appears to be an open-source project):

1. **Make the repository public** on Docker Hub
2. This allows Portainer and anyone else to pull the image without authentication
3. Keep your source code repository private if needed (Docker images can still be public)

For production/private deployments:

1. **Add Docker Hub credentials** to Portainer as a registry
2. Keep the repository private
3. Use access tokens instead of passwords for better security
