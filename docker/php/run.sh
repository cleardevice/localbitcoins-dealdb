DIR=$(dirname $(realpath $0))
docker run -d -p 8080:8080 -v $DIR/../../data:/data --link dealdb-redis:redis --name dealdb-php dealdb-php-im
