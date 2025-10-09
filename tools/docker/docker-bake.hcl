variable "TAG" {
  default = "latest"
}

variable "REGISTRY" {
  default = "docker.io/robjects/whatismyadapter_cms"
}

target "willowcms" {
  context = "."
  dockerfile = "docker/willowcms/Dockerfile"
  tags = ["${REGISTRY}:${TAG}"]
  args = {
    UID = "1000"
    GID = "1000"
  }
}

target "jenkins" {
  context = "."
  dockerfile = "docker/jenkins/Dockerfile"
  tags = ["jenkins:latest"]
}

group "default" {
  targets = ["willowcms"]
}

group "all" {
  targets = ["willowcms", "jenkins"]
}
