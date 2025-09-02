# WhatIsMyAdaptor
Webapp project containing useful adapter (C, USB, etc.,) all in one cohesive place.

Aiming to answer the common problem of trying to find the connection adapter for your specific needs with specific links without having to go through all the hassle.

## Table of Contents

- [WhatIsMyAdaptor](#whatismyadaptor)
  - [Table of Contents](#table-of-contents)
  - [🚀 Quick Start](#-quick-start)
    - [Prerequisites](#prerequisites)
    - [Installation](#installation)
      - [Quick Run - Existing Installation](#quick-run---existing-installation)
      - [Quick Run - Fresh Installation](#quick-run---fresh-installation)
    - [Additional Services](#additional-services)
  - [Managing the Application](#managing-the-application)
  - [Using `manage.sh`](#using-managesh)
  - [Using script aliases from `setup_dev_aliases.sh`](#using-script-aliases-from-setup_dev_aliasessh)
    - [Useful Aliases](#useful-aliases)
    - [CakePHP Command Aliases](#cakephp-command-aliases)
    - [Using the Project Container](#using-the-project-container)
  - [Extra Notes](#extra-notes)
- [Troubleshooting](#troubleshooting)
  - [Useful Links](#useful-links)
  - [Contributing to Projects](#contributing-to-projects)
  - [License](#license)
  - [Acknowledgements](#acknowledgements)


---

## 🚀 Quick Start

### Prerequisites
- [Docker](https://www.docker.com/get-started) (only requirement on your host machine)
- Git

### Installation

#### Quick Run - Existing Installation

Code below will install all necessary dependencies and set up the development environment.
```bash
# Clone the repository
git clone https://github.com/Robjects-Community/WhatIsMyAdaptor.git
cd WhatIsMyAdaptor

# Run the setup script
./setup_dev_env.sh
```

before finishing the script, you should be prompted to type the letter **(w/b/r/m/c)** corresponding to your preferred options for the development environment setup, options include:

  - Wipe data: letter 'W'
  - Rebuild containers: letter 'B'
  - Restart services: letter 'R'
  - Run migrations: letter 'M'
  - Continue with the current setup: letter 'C'


> Note:
> This quick start guide assumes that you have already run `docker-compose up -d` or `docker compose up -d` to start the containers and that docker compose is set up correctly.


#### Quick Run - Fresh Installation

1) Install docker
2) Clone repo
3) cd into folder
4) `docker-compose up -d` or `docker compose up -d`
5) Wait for the containers to be fully up and running
6) Then run setup_dev_env.sh
7) Run the aliases script and feel free to use the scripts going forward! :)

```bash
# Clone the repository
git clone https://github.com/Robjects-Community/WhatIsMyAdaptor.git
cd WhatIsMyAdaptor

# Test Docker Compose
docker-compose up -d

# Check if the containers are running
docker-compose ps
```


🎉 **That's it!** Your development environment is ready:

- **Main Site**: [http://localhost:8080](http://localhost:8080)
- **Admin Panel**: [http://localhost:8080/admin](http://localhost:8080/admin)
  - **Login**: `admin@test.com` / `password`

### Additional Services

- **phpMyAdmin**: [http://localhost:8082](http://localhost:8082) (root/password)
- **Mailpit**: [http://localhost:8025](http://localhost:8025) (email testing)
- **Redis Commander**: [http://localhost:8084](http://localhost:8084) (root/password)
- **Jenkins**: [http://localhost:8081](http://localhost:8081) (start with `./setup_dev_env.sh --jenkins`)

---


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
Here are the following aliases that would help manage the project:

- `willow_shell`: Run cake in the main project container
- `willowcms_shell`: Get a bash shell in the main project container (alias for `willow_shell`)

### CakePHP Command Aliases
Here are some useful aliases for running CakePHP commands:

```bash
willow_shell
```


> Note: Running `willow_shell` is the equivalent as running `bin/cake` inside the container. Therefore, you can use `willow_shell` to run any cakephp command without having to enter `bin/cake` before every command.

### Using the Project Container

1) Get into the main project container using `docker-compose exec [NAME_OF_YOUR_CONTAINER] /bin/bash` (or alias `willowcms_shell` after setting up aliases)
2) Once in the container, run `bin/cake migrations migrate` to migrate the DB just like you would in a normal cakephp setup
3) Terminal in the container and run `bin/cake migrations seed` to seed the DB


## Extra Notes

> Note: The project doesn't contain any initial seed data as all seeding is already managed through commands in the `manage.sh` scripts. You will need to create your own seed files in the `config/Seeds` directory if you plan on using the seeding functionality according to the CakePHP documentation.


---

# Troubleshooting
## Useful Links

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [CakePHP Documentation (CakePHP5.X.X)](https://book.cakephp.org/5/en/index.html#/)

## Contributing to Projects
Contributions are welcome! Please fork the repository and create a pull request with your changes. Make sure to follow the coding standards and include tests for any new features or bug fixes.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


## Acknowledgements

<!--
### Special Thanks
I want to give a huge shoutout to **Matthew** for his invaluable GitHub contributions to the base project.

Developers like him make open-source projects thrive. He has been instrumental in helping me set up this project and navigate the complexities of Docker and CakePHP. Projects like these are exactly why I advocate great open-source and I plan on featuring him in an upcoming video.

Future project video coming soon! - Mike

-->
