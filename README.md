# WhatIsMyAdaptor
Webapp project containing useful adapter (C, USB, etc.,) all in one cohesive place.

Aiming to answer the common problem of trying to find the connection adapter for your specific needs with specific links without having to go through all the hassle.

## Table of Contents

- [WhatIsMyAdaptor](#whatismyadaptor)
  - [Table of Contents](#table-of-contents)
- [Managing the Application](#managing-the-application)
  - [Using `manage.sh`](#using-managesh)
  - [Using script aliases from `setup_dev_aliases.sh`](#using-script-aliases-from-setup_dev_aliasessh)
    - [Using Aliases](#using-aliases)
    - [Using the Project Container](#using-the-project-container)
  - [Running the Code for Local Development](#running-the-code-for-local-development)
  - [Useful Links](#useful-links)
  - [Contributing to Projects](#contributing-to-projects)
  - [License](#license)



Software setup:
1) Install docker
2) Clone repo
3) cd into folder
4) `docker-compose up -d`
5) Wait for the containers to be fully up and running
6) Run `./setup_dev_env.sh` to set up the project
7) Run `./setup_dev_aliases.sh` to set up aliases for easier access to the containers

# Managing the Application

## Using `manage.sh`

`manage.sh` is a script that can be used that simplifies common tasks needed to manage the application.

This contains predefined commands for common tasks. Use `./manage.sh <command>` to run a command (or just `./manage.sh` to see a list of available commands).

## Using script aliases from `setup_dev_aliases.sh`

Ensure you have sourced the `setup_dev_aliases.sh` 
and have added the necessary details needed in your specific .*rc file. Then run:
```bash
source ./setup_dev_aliases.sh
```
You should now be able to use the defined aliases in your terminal session, look at 'willow-a

### Using Aliases

Once you have setup aliases, you can use them to run commands in the project container more easily. 
Here are the following aliases that would help manage the project:


- Get a bash shell in the main project container
```bash
willowcms_shell
```
- run project-specific cakephp commands
```bash
willow_shell     
```

> Note:
> All cakephp commands should be run inside the project container. You should be able to utilize all standard cakephp commands in case you prefer using the command line and terminal.


### Using the Project Container

1) Get into the main project container using `docker-compose exec [NAME_OF_YOUR_CONTAINER] /bin/bash` (or alias `willow)
2) Terminal in the container and run `bin/cake migrations migrate` once you are in the container to migrate the DB
3) Terminal in the container and run `bin/cake migrations seed` to seed the DB



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
