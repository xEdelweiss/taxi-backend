import Alpine from 'alpinejs';
import L from 'leaflet';
import TaxiUtils from './TaxiUtils.js';
import Map from './Map.js';

export default function useAdminClient() {
  // keep outside the Data to prevent wrapping it in a Proxy
  let map = null;

  Alpine.data('adminClient', () => ({
    showControls: true,
    people: [],
    coordinates: {},
    interval: null,

    get users() {
      return this.people.filter(user => user.type === 'user');
    },

    get drivers() {
      return this.people.filter(user => user.type === 'driver');
    },

    async init() {
      this.$nextTick(() => this._initMap('admin-map'));
      this.$watch('people', () => this._refreshMarkers());
      this.$watch('coordinates', () => this._redrawMarkers());

      await this.refreshUsers();

      if (this.users.length === 0) {
        await this.addUser();
      }

      if (this.drivers.length === 0) {
        await this.addDriver();
      }

      this._redrawMarkers();

      this.selectUser(this.users[0].phone);
      this.selectDriver(this.drivers[0].phone);

      // this.interval = setInterval(async () => {
      //   await this.refreshUsers(true);
      // }, 1000);
    },

    // destroy() {
    //   console.log('destroy');
    //   clearInterval(this.interval);
    // },

    _initMap(id) {
      map = new Map(id, 14);

      const coordsPopup = L.popup();
      map.onClick((e) => {
        coordsPopup
          .setLatLng(e.latlng)
          .setContent(e.latlng.toString())
          .openOn(map);
      });
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
            this.people = data;
          }
        });
    },

    _refreshMarkers() {
      this.people.forEach(user => {
        if (!map.hasMarker(`phone-${user.phone}`)) {
          map.addMarker(
            `phone-${user.phone}`,
            L.marker([0, 0], {
                icon: Map.getIcon(user.type),
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

          );
        }
      });

      // remove markers for users that are no longer in the list
      const phones = this.people.map(user => user.phone);
      map.getMarkersIds().forEach(id => {
        const phone = id.replace('phone-', '');

        if (!phones.includes(phone)) {
          map.removeMarker(id);
        }
      });
    },

    _redrawMarkers() {
      this.people.forEach(user => this._moveMarker(user));
    },

    _moveMarker(user) {
      if (!map) {
        console.log('no coords or map');
        return;
      }

      const latLng = this.coordinates[user.phone] ? [
        this.coordinates[user.phone].latitude,
        this.coordinates[user.phone].longitude,
      ] : null;

      map.moveMarker(`phone-${user.phone}`, latLng, false, 1250);
    },

    addUser: async function () {
      return this._makeUser('user')
        .then(data => {
          this.people.push(data);
        });
    },

    addDriver: async function () {
      return this._makeUser('driver')
        .then(data => {
          this.drivers.push(data);
        });
    },

    _makeUser: function (type) {
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

    removeUser: function (phone) {
      this._deletePerson(phone)
        .then(() => {
          this.people = this.people.filter(user => user.phone !== phone);
          this.$dispatch('user-removed', {phone});
          console.log('Event:', 'user-removed', {phone});
        });
    },

    removeDriver: function (phone) {
      this._deletePerson(phone)
        .then(() => {
          this.drivers = this.drivers.filter(driver => driver.phone !== phone);
          this.$dispatch('driver-removed', {phone});
          console.log('Event:', 'driver-removed', {phone});
        });
    },

    _deletePerson: function (phone) {
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

    selectUser: function (phone) {
      this.$dispatch('user-selected', {phone});
      console.log('Event:', 'user-selected', {phone});
    },

    selectDriver: function (phone) {
      this.$dispatch('driver-selected', {phone});
      console.log('Event:', 'driver-selected', {phone});
    },
  }));
}
