# Demo Taxi API

This project is a demonstration of a taxi API that facilitates ordering a taxi from point A to point B.

All features are covered by tests using Codeception and PHPUnit.

> **Important!** This project is intended for demonstration purposes only and should not be used in a production
> environment. Although implementing validation and authorization mechanisms is planned, it is not a top priority at the
> moment.

## Todo

- [ ] Debug UI
- [ ] Simulate driver/user actions
- [x] Registration and authentication
- [ ] Driver profile activation
- [x] Geocoding and reverse geocoding
    - [x] OpenStreetMap Nominatim service
- [x] Location tracking
- [x] Route planning
    - [x] OpenStreetRoute self-hosted service
    - [ ] Route rendering
- [x] Cost estimation
    - [x] Simple distance-based cost estimation
    - [ ] Advanced cost estimation
- [ ] Payment processing
    - [x] Stripe integration
    - [x] Hold payment until the order is completed
    - [x] Charge payment if the order is completed
    - [ ] Refund payment if the order is canceled
- [ ] Driver matching
    - [x] Shortest distance matching strategy
    - [x] Fastest delivery time matching strategy
    - [ ] Retry matching if no drivers are available
- [ ] Order management
    - [x] Order creation
    - [ ] Order cancellation
    - [x] Order completion
- [ ] Rating system

## Development Environment

### Start

```bash
symfony serve
```

### Preparation

#### OpenStreetRoute service
1. Download OSM data from [OSM](https://www.openstreetmap.org/export) - use Overpass API to export large chunk of data
2. Put the data into `var/ors-docker/files/map-odesa-test.osm` or change the file name
   in `var/ors-docker/config/ors-config.yml:102`
3. Run `docker-compose up` to start the service (`REBUILD_GRAPHS` should be `True` to rebuild the graphs)

## Production Environment

### Deployment

* Compile assets: `php bin/console asset-map:compile`

## Notes

Custom OSM tiles: https://leaflet-extras.github.io/leaflet-providers/preview/

## Notes

* Build API documentation:
  ```bash
  php bin/console nelmio:apidoc:dump --format=html > api.html
  php bin/console nelmio:apidoc:dump --format=json > api.json
  ```
