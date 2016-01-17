DIR=$(dirname $(realpath $0))
docker run -v $DIR/data:/data -p 6379:6379 --name dealdb-redis -d cleardevice/redis
