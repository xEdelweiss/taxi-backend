import Alpine from 'alpinejs';
import L from 'leaflet';
import TaxiMap from './TaxiMap.js';
import TaxiApi from './TaxiApi.js';

export default function useUserClient() {
  Alpine.data('userClient', () => ({
    selectedPhone: null,
    token: null,
    map: null,
    userMarker: null,
    userLatLng: [0, 0],
    address: '',
    init() {
      this.$nextTick(() => this._initMap('client-map'));
      this.$watch('userLatLng', (latLng) => this._moveMarker(latLng));
      this.$watch('userLatLng', (latLng) => this._fetchAddress(latLng));
      this.$watch('userLatLng', (latLng) => this._saveLocation(latLng));
      this.$watch('selectedPhone', async (phone, oldPhone) => {
        if (oldPhone !== phone) {
          this.token = null;
        }

        this._login(phone);
      });
    },
    _initMap(id) {
      this.map = TaxiMap.createMap(id);

      this.map.on('click', (e) => {
        this.userLatLng = [e.latlng.lat, e.latlng.lng];
      });
    },
    _moveMarker(latLng) {
      if (!latLng || !this.map) {
        console.log('no coords or map');
        return;
      }

      if (!this.userMarker) {
        this.userMarker = L.marker(latLng, {icon: TaxiMap.getUserIcon(1.5)}).addTo(this.map);
      } else {
        this.userMarker.setLatLng(new L.LatLng(...latLng));
      }

      TaxiMap.moveMap(this.map, latLng);
    },
    async _fetchAddress(latLng) {
      try {
        this.address = await TaxiApi.fetchAddressByCoords(latLng, this.token);
      } catch (e) {
        console.error(e);
      }
    },
    async _saveLocation(latLng) {
      try {
        await TaxiApi.saveLocation(latLng, this.token);

        this.$dispatch('user-moved', {phone: this.selectedPhone});
      } catch (e) {
        console.error(e);
      }
    },
    async _login(phone) {
      if (this.token || !phone) {
        return;
      }

      const {token, latLng} = await TaxiApi.login(phone);

      this.token = token;
      this.userLatLng = latLng;
    },
    async findAddress() {
      try {
        this.userLatLng = await TaxiApi.fetchCoordsByAddress(this.address, this.token);
      } catch (e) {
        console.error(e);
      }
    },
  }));
}
