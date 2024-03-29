# Preparing dev env

## OpenStreetRoute service
1. Download OSM data from [OSM](https://www.openstreetmap.org/export) - use Overpass API to export large chunk of data
2. Put the data into `var/ors-docker/files/map-odesa-test.osm` or change the file name in `var/ors-docker/config/ors-config.yml:102`
3. Run `docker-compose up` to start the service (`REBUILD_GRAPHS` should be `True` to rebuild the graphs)
