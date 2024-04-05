import Alpine from 'alpinejs';
import L from 'leaflet';
import TaxiMap from './TaxiMap.js';
import TaxiUtils from './TaxiUtils.js';

export default function useAdminClient() {
  Alpine.data('adminClient', () => ({
    showControls: true,
    everybody: [],
    markers: {},
    coordinates: {},
    interval: null,

    get users() {
      return this.everybody.filter(user => user.type === 'user');
    },

    get drivers() {
      return this.everybody.filter(user => user.type === 'driver');
    },

    async init() {
      this.$nextTick(() => this._initMap('admin-map'));
      this.$watch('coordinates', () => this._redrawMarkers());

      await this.refreshUsers();
      this._redrawMarkers();

      if (this.everybody.length === 0) {
        await this.addUser();
      }

      if (this.drivers.length === 0) {
        await this.addDriver();
      }

      this.selectUser(this.users[0].phone);
      this.selectDriver(this.drivers[0].phone);

      // this.interval = setInterval(async () => {
      //   await this.refreshUsers(true);
      //   this._redrawMarkers();
      // }, 1000);
    },
    // destroy() {
    //   console.log('destroy');
    //   clearInterval(this.interval);
    // },
    _initMap(id) {
      this.map = TaxiMap.createMap(id, 14);

      const coordsPopup = L.popup();
      function onMapClick(e) {
        coordsPopup
          .setLatLng(e.latlng)
          .setContent(e.latlng.toString())
          .openOn(this.map);
      }
      this.map.on('click', onMapClick);
    },
    refreshUsers(onlyLocations = false) {
      return fetch('/debug/users')
        .then(response => response.json())
        .then(({data}) => {
          this.coordinates = data.reduce((acc, user) => {
            acc[user.phone] = user.coordinates;
            return acc;
          }, {});

          if (!onlyLocations) {
            this.everybody = data;
          }
        });
    },
    _redrawMarkers() {
      this.everybody.forEach(user => this._moveMarker(user));
    },
    _moveMarker(user) {
      if (!this.coordinates[user.phone] || !this.map) {
        console.log('no coords or map');
        return;
      }

      const latLng = [
        this.coordinates[user.phone].latitude,
        this.coordinates[user.phone].longitude,
      ];

      if (!this.markers[user.phone]) {
        this.markers[user.phone] = L.marker(latLng, {
            icon: TaxiMap.getIcon(user.type),
          })
          .bindTooltip(TaxiUtils.formatPhone(user.phone), {
            permanent: false,
            direction: 'right',
            offset: [16, -20],
            className: 'font-semibold text-sm bg-white text-gray-800 p-2 rounded-md shadow-md',
          })
          .on('click', () => {
            if (user.type === 'user') {
              this.selectUser(user.phone);
            } else {
              this.selectDriver(user.phone);
            }
          })
          .addTo(this.map);
      } else {
        this.markers[user.phone].setLatLng(new L.LatLng(...latLng));
      }
    },
    addUser: async function() {
      return this._makeUser('user')
        .then(data => {
          this.everybody.push(data);
        });
    },
    addDriver: async function() {
      return this._makeUser('driver')
        .then(data => {
          this.drivers.push(data);
        });
    },
    _makeUser: function(type) {
      return fetch('/debug/users', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({type}),
      })
        .then(response => response.json())
        .then(({data}) => data);
    },
    removeUser: function(phone) {
      this._deleteUser(phone)
        .then(() => {
          this.everybody = this.everybody.filter(user => user.phone !== phone);
          this.$dispatch('user-removed', {phone});
          console.log('Event:', 'user-removed', {phone});
        });
    },
    removeDriver: function(phone) {
      this._deleteUser(phone)
        .then(() => {
          this.drivers = this.drivers.filter(driver => driver.phone !== phone);
          this.$dispatch('driver-removed', {phone});
          console.log('Event:', 'driver-removed', {phone});
        });
    },
    _deleteUser: function(phone) {
      return fetch('/debug/users', {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({phone}),
      })
        .then(response => response.json())
        .then(({data}) => data);
    },
    selectUser: function(phone) {
      this.$dispatch('user-selected', {phone});
      console.log('Event:', 'user-selected', {phone});
    },
    selectDriver: function(phone) {
      this.$dispatch('driver-selected', {phone});
      console.log('Event:', 'driver-selected', {phone});
    },
  }));
}
