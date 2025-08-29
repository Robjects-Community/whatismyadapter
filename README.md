# WhatIsMyAdaptor
Webapp project containing useful adapter (C, USB, etc.,) all in one cohesive place.

Aiming to answer the common problem of trying to find the connection adapter for your specific needs with specific links without having to go through all the hassle.

## Table of Contents

- [WhatIsMyAdaptor](#whatismyadaptor)
  - [Table of Contents](#table-of-contents)
  - [Quick Start](#quick-start)
  - [Managing the Application](#managing-the-application)
  - [Using `manage.sh`](#using-managesh)
  - [Using script aliases from `setup_dev_aliases.sh`](#using-script-aliases-from-setup_dev_aliasessh)
    - [Useful Aliases](#useful-aliases)
      - [Project Management Aliases](#project-management-aliases)
      - [CakePHP Command Aliases](#cakephp-command-aliases)
    - [Using the Project Container](#using-the-project-container)
  - [Useful Links](#useful-links)
  - [Contributing to Projects](#contributing-to-projects)
  - [License](#license)


## Quick Start
Software setup:

1) Install docker
2) Clone repo
3) cd into folder
4) `docker-compose up -d` or `docker compose up -d`
5) Wait for the containers to be fully up and running
6) After the containers are running, you can access the application at `http://localhost:8080`
7) Run `./setup_dev_env.sh` to set up the project
8) Run `./setup_dev_aliases.sh` to set up aliases for easier access to the containers

## Managing the Application

## Using `manage.sh`

`manage.sh` is a script that can be used that simplifies common tasks needed to manage the application.

This contains predefined commands for common tasks. Use `./manage.sh <command>` to run a command (or just `./manage.sh` to see a list of available commands).

## Using script aliases from `setup_dev_aliases.sh`

Ensure you have sourced the `setup_dev_aliases.sh` 
and have added the necessary details needed in your specific .*rc file. Then run:
```bash
source ./setup_dev_aliases.sh
```
You should now be able to use the defined aliases in your terminal session, look at 'dev_aliases.txt' for more information on what aliases are available.

### Useful Aliases
Once you have setup aliases, you can use them to run commands in the project container more easily. 
Here are the following aliases that would help manage the project:

#### Project Management Aliases

- Get a bash shell in the main project container
```bash
willowcms_shell
```

#### CakePHP Command Aliases
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

## Useful Links

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [CakePHP Documentation (CakePHP5.X.X)](https://book.cakephp.org/5/en/index.html#/)

## Contributing to Projects
Contributions are welcome! Please fork the repository and create a pull request with your changes. Make sure to follow the coding standards and include tests for any new features or bug fixes.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
