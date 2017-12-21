# How to run with Docker on Windows

From Docker terminal, move to the project's directory.
Build the Docker image.
docker run -dp 80:80 -v "$PWD/symfony/":/var/www/html/ --rm --name lm symfony