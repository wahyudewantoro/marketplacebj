cd /bapenda/marketplacebj/
git pull origin
cd /bapenda/marketplacebj/
docker compose -f docker-compose.yml up --build -d
docker rmi $(docker images --filter "dangling=true" -q --no-trunc)
