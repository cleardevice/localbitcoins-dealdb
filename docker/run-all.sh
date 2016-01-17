DIR=$(dirname $(realpath $0))
cd $DIR/redis && sh run.sh
cd $DIR/php && sh run.sh
