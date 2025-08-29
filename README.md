# WhatIsMyAdaptor
Webapp project containing useful adapter (C, USB, etc.,) all in one cohesive place.

Aiming to answer the common problem of trying to find the connection adapter for your specific needs with specific links without having to go through all the hassle.

## Table of Contents

- [WhatIsMyAdaptor](#whatismyadaptor)
  - [Table of Contents](#table-of-contents)
  - [Instructions](#instructions)
  - [Running the Code for Local Development](#running-the-code-for-local-development)
  - [Useful Links](#useful-links)
  - [Contributing to Projects](#contributing-to-projects)
  - [License](#license)


## Instructions

Software setup:
1) Install docker
2) Clone repo
3) cd into folder
4) `docker-compose up -d`
5) terminal in the container and run `bin/cake migrations migrate` to migrate the DB
6) terminal in the container and run `bin/cake migrations seed` to seed the DB



<!-- 3. Development Setup of Project -->
<!-- TODO: Describe the development setup of the project -->

- [ Development Setup](#development-setup)

<!-- 4. Running the Code for Local Development -->
## Running the Code for Local Development
After ensuring that docker is installed and running, you can start the application by running the docker-compose command:
```bash
docker-compose up -d
```

After the docker compose image is built from the 'docker-compose.yml' file, you can start the containers and use the scripts to manage the project

Here are some useful scripts included in the project:
  - 'setup.sh': Set up the project
  - 'build.sh': Build the application
  - 'setup_dev_env.sh': Set up the development environment
  - 'setup_dev_aliases.sh': Set up development aliases
  - 'manage.sh': Manage the application
  - 'wait-for-it.sh': Wait for the application to be ready



## Useful Links

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [CakePHP Documentation](https://book.cakephp.org/5/en/index.html#/)

## Contributing to Projects
Contributions are welcome! Please fork the repository and create a pull request with your changes. Make sure to follow the coding standards and include tests for any new features or bug fixes.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
