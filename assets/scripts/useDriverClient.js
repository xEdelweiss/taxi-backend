import Alpine from 'alpinejs';
import L from 'leaflet';
import TaxiApi from './TaxiApi.js';
import Map from './Map.js';

export default function useDriverClient() {
  // keep outside the Data to prevent wrapping it in a Proxy
  let map = null;

  Alpine.data('driverClient', () => ({
      selectedPhone: null,
      token: null,

      driverLatLng: null,

      startLatLng: null,
      startAddress: '',

      endLatLng: null,
      endAddress: '',

      // @fixme order should be separated from order request
      order: null,

      init() {
        this.$nextTick(() => this._initMap('driver-map'));

        this.$watch('driverLatLng', (latLng) => map.moveMarker('driver', latLng, true));
        this.$watch('driverLatLng', (latLng) => this._saveLocation(latLng)); // we are faking driver's movement

        this.$watch('order', () => this._refreshOrderRoute());
        this.$watch('driverLatLng', () => this._refreshRouteToStart());

        this.$watch('selectedPhone', async (phone, oldPhone) => {
          if (oldPhone !== phone) {
            this._reset();
          }

          await this._login(phone);
        });
      },

      _initMap(id) {
        map = new Map(id);

        map.addMarker('driver', makeMarker([0, 0]));

        map.onClick((e) => {
          this.driverLatLng = [e.latlng.lat, e.latlng.lng];
        });
      },

      _reset() {
        this.token = null;

        this.driverLatLng = null;
      },

      async _saveLocation(latLng) {
        if (!this.token || !latLng) {
          return;
        }

        try {
          await TaxiApi.saveLocation(latLng, this.token);

          this.$dispatch('driver-moved', {phone: this.selectedPhone});
        } catch (e) {
          console.warn(e);
        }
      },

      async _refreshOrderRoute() {
        if (!this.token || !this.order) {
          map.removeMarker('order-route');
          map.removeMarker('order-start');
          map.removeMarker('order-end');
          return;
        }

        try {
          const startLatLng = [this.order.start.latitude, this.order.start.longitude];
          const endLatLng = [this.order.end.latitude, this.order.end.longitude];

          const route = await TaxiApi.fetchRoute(
            startLatLng,
            this.order.start.address,
            endLatLng,
            this.order.end.address,
            this.token,
          );

          const points = Map.decodePolyline(route.polyline);

          map.replaceMarker(
            'order-route',
            new L.Polyline(points, {
              color: 'blue',
              weight: 8,
              opacity: this.order.status === 'WAITING_FOR_DRIVER' ? 0.4 : 0.7,
              smoothFactor: 1,
            })
          );

          map.addMarker('order-start', makeMarker(startLatLng, 'client', 'Start', 1));
          map.addMarker('order-end', makeMarker(endLatLng, 'client', 'Finish', 1));

          map.fitBounds([
            [route.boundingBox.bottomLeft.latitude, route.boundingBox.bottomLeft.longitude],
            [route.boundingBox.topRight.latitude, route.boundingBox.topRight.longitude],
          ]);
        } catch (e) {
          console.warn('fetch route error', e);
        }
      },

      async _refreshRouteToStart() {
        if (!this.token || !this.driverLatLng || !this.order) {
          map.removeMarker('route-to-order');
          return;
        }

        try {
          const route = await TaxiApi.fetchRoute(
            this.driverLatLng,
            '',
            [this.order.start.latitude, this.order.start.longitude],
            this.order.start.address,
            this.token,
          );

          const points = Map.decodePolyline(route.polyline);

          map.replaceMarker(
            'route-to-order',
            new L.Polyline(points, {
              color: 'orange',
              weight: 8,
              opacity: 1,
              smoothFactor: 1,
            })
          );

          map.fitBounds([
            [route.boundingBox.bottomLeft.latitude, route.boundingBox.bottomLeft.longitude],
            [route.boundingBox.topRight.latitude, route.boundingBox.topRight.longitude],
          ]);
        } catch (e) {
          console.warn('fetch route error', e);
        }
      },

      async _login(phone) {
        if (this.token || !phone) {
          return;
        }

        try {
          const {token, latLng, orderRequest, order} = await TaxiApi.login(phone);

          this.token = token;
          this.driverLatLng = latLng;
          this.order = order;
        } catch (e) {
          console.warn(e);
        }
      },
    })
  );

  function makeMarker(latLng, type = 'driver', tooltip = '', scale = 1.5) {
    const marker = L.marker(latLng, {icon: Map.getIcon(type, scale)});

    if (tooltip) {
      marker.bindTooltip(tooltip, {
        permanent: true,
        direction: 'right',
        offset: [(16 * 1.5), (-20 * 1.5)],
        className: 'font-semibold text-sm bg-white text-gray-800 p-2 rounded-md shadow-md',
      });
    }

    return marker;
  }
}
