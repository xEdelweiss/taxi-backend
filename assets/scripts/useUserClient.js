import Alpine from 'alpinejs';
import L from 'leaflet';
import TaxiApi from './TaxiApi.js';
import Map from "./Map.js";
import TaxiUtils from "./TaxiUtils.js";

export default function useUserClient() {
  // keep outside the Data to prevent wrapping it in a Proxy
  let map = null;

  Alpine.data('userClient', () => ({
      selectedPhone: null,
      token: null,

      selectedAddress: 'start', // start, end

      startLatLng: null,
      startAddress: '',

      endLatLng: null,
      endAddress: '',

      estimatedCost: {
        cost: 0,
        currency: 'USD',
      },

      order: null,

      get orderStep() {
        if (['WAITING_FOR_DRIVER', 'DRIVER_ON_WAY', 'DRIVER_ARRIVED'].includes(this.order?.status)) {
          return 1;
        } else if (this.order?.status === 'COMPLETED') {
          return 2;
        }

        return 0;
      },

      get formattedCost() {
        return TaxiUtils.formatMoney(this.estimatedCost);
      },

      get activeAddress() {
        return this.selectedAddress === 'start' ? this.startAddress : this.endAddress;
      },

      set activeAddress(address) {
        if (this.selectedAddress === 'start') {
          this.startAddress = address;
        } else {
          this.endAddress = address;
        }
      },

      get activeLatLng() {
        return this.selectedAddress === 'start' ? this.startLatLng : this.endLatLng;
      },

      set activeLatLng(latLng) {
        if (this.selectedAddress === 'start') {
          this.startLatLng = latLng;
        } else {
          this.endLatLng = latLng;
        }
      },

      init() {
        this.$nextTick(() => this._initMap('client-map'));

        this.$watch('startLatLng', (latLng) => map.moveMarker('start', latLng, !this.endLatLng));
        this.$watch('endLatLng', (latLng) => map.moveMarker('end', latLng));

        this.$watch('startLatLng', (latLng) => this._saveLocation(latLng)); // we are faking user's movement

        this.$watch('activeLatLng', (latLng) => this._fetchAddress(latLng));
        this.$watch('activeLatLng', () => this._refreshRoute());

        this.$watch('selectedPhone', async (phone, oldPhone) => {
          if (oldPhone !== phone) {
            this._reset();
          }

          await this._login(phone);
        });
      },

      _initMap(id) {
        map = new Map(id);

        map.addMarker('start', makeMarker([0, 0], 'Start'));
        map.addMarker('end', makeMarker([0, 0], 'Finish'));

        map.onClick((e) => {
          if (this.order) {
            return;
          }

          this.activeLatLng = [e.latlng.lat, e.latlng.lng];
        })
      },

      _reset() {
        this.token = null;

        this.startLatLng = null;
        this.endLatLng = null;

        this.startAddress = '';
        this.endAddress = '';

        map.removeMarker('route');

        this.selectedAddress = 'start';
      },

      async _fetchAddress(latLng) {
        if (!this.token || !latLng) {
          return;
        }

        try {
          this.activeAddress = await TaxiApi.fetchAddressByCoords(latLng, this.token);

          if (this.selectedAddress === 'end') {
            await Promise.all([
              this._refreshRoute(),
              this._refreshEstimation(),
            ]);
          }
        } catch (e) {
          console.warn(e);
        }
      },

      async _saveLocation(latLng) {
        if (!this.token || !latLng) {
          return;
        }

        try {
          await TaxiApi.saveLocation(latLng, this.token);

          this.$dispatch('user-moved', {phone: this.selectedPhone});
        } catch (e) {
          console.warn(e);
        }
      },

      async _refreshRoute() {
        if (!this.token || !this.startLatLng || !this.endLatLng) {
          return;
        }

        try {
          const route = await TaxiApi.fetchRoute(
            this.startLatLng,
            this.startAddress,
            this.endLatLng,
            this.endAddress,
            this.token,
          );

          const points = Map.decodePolyline(route.polyline);

          map.replaceMarker(
            'route',
            new L.Polyline(points, {
              color: 'blue',
              weight: 8,
              opacity: 0.7,
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

      async _refreshEstimation() {
        if (!this.token || !this.startLatLng || !this.endLatLng) {
          return;
        }

        try {
          this.estimatedCost = await TaxiApi.fetchEstimation(
            this.startLatLng,
            this.startAddress,
            this.endLatLng,
            this.endAddress,
            this.token,
          );
        } catch (e) {
          console.warn('fetch route error', e);
        }
      },

      async _login(phone) {
        if (this.token || !phone) {
          return;
        }

        const {token, latLng, order} = await TaxiApi.login(phone);

        this.token = token;
        this.startLatLng = latLng;
        this.order = order;

        if (this.order) {
          this.startAddress = this.order.start.address;
          this.endAddress = this.order.end.address;

          this.startLatLng = [this.order.start.latitude, this.order.start.longitude];
          this.endLatLng = [this.order.end.latitude, this.order.end.longitude];
        }
      },

      async findAddress() {
        try {
          this.activeLatLng = await TaxiApi.fetchCoordsByAddress(this.activeAddress, this.token);
        } catch (e) {
          console.warn(e);
        }
      },

      async createOrder() {
        if (!this.token || !this.startLatLng || !this.endLatLng) {
          return;
        }

        try {
          this.order = await TaxiApi.createOrder(
            this.startLatLng,
            this.startAddress,
            this.endLatLng,
            this.endAddress,
            this.token,
          );
        } catch (e) {
          console.warn('create order error', e);
        }
      },

      async payOrder() {
        if (!this.token || !this.order) {
          return;
        }

        try {
          await TaxiApi.payOrder(this.order.id, this.token);
          this.order = TaxiApi.fetchOrder(this.token, this.order.id);
        } catch (e) {
          console.warn('pay order error', e);
        }
      },
    }),
  );

  function makeMarker(latLng, tooltip) {
    return L.marker(latLng, {icon: Map.getUserIcon(1.5)})
      .bindTooltip(tooltip, {
        permanent: true,
        direction: 'right',
        offset: [(16 * 1.5), (-20 * 1.5)],
        className: 'font-semibold text-sm bg-white text-gray-800 p-2 rounded-md shadow-md',
      });
  }
}
