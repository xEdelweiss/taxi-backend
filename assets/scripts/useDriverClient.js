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

      init() {
        this.$nextTick(() => this._initMap('driver-map'));

        this.$watch('driverLatLng', (latLng) => map.moveMarker('driver', latLng, true));
        this.$watch('driverLatLng', (latLng) => this._saveLocation(latLng)); // we are faking driver's movement

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

      async _login(phone) {
        if (this.token || !phone) {
          return;
        }

        try {
          const {token, latLng} = await TaxiApi.login(phone);

          this.token = token;
          this.driverLatLng = latLng;
        } catch (e) {
          console.warn(e);
        }
      },
    })
  );

  function makeMarker(latLng) {
    return L.marker(latLng, {icon: Map.getDriverIcon(1.5)});
  }
}
