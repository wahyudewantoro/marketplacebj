cd /bapenda/pembayaranpbb/
git pull origin
cd /bapenda/pembayaranpbb/
docker compose -f docker-compose.yml up --build -d
docker rmi $(docker images --filter "dangling=true" -q --no-trunc)
