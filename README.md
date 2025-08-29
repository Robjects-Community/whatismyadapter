# WhatIsMyAdaptor
Webapp project containing useful adapter (C, USB, etc.,) all in one cohesive place.

Aiming to answer the common problem of trying to find the connection adapter for your specific needs with specific links without having to go through all the hassle.

## Table of Contents

- [WhatIsMyAdaptor](#whatismyadaptor)
  - [Table of Contents](#table-of-contents)
  - [Instructions](#instructions)
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
  - Install necessary dependencies
  - Set up your development environment 
- Set up your environment variables in the `.env` file
- Make sure to configure your database settings

<!-- 4. Running the Code for Local Development -->
<!-- TODO: Explain how to run the code for local development -->

- [Running the Code for Local Development](#running-the-code-for-local-development)
  - First build the Docker images: `docker-compose build`
  - Once the images are built, you can start the containers and use the scripts to manage the project
    - 
  - Make sure to follow any additional setup instructions specific to the project
  - Use `docker-compose up` to start the application
  - Access the application at `http://localhost:8080`



## Contributing to Projects

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
