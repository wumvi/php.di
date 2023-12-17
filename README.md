```bash
docker run -ti --rm -v "$(pwd)":/data/ --workdir /data/ --network host php:8.3.0-cli-bullseye php composer.phar install
```