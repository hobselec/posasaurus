# Getting Started

## Installation
```js
// npm
npm i vue-bootstrap-autocomplete

// yarn
yarn add vue-bootstrap-autocomplete
```

## Registration

```js
import VueBootstrapAutocomplete from 'vue-bootstrap-autocomplete';

// Required dependency of bootstrap css/scss files
import 'bootstrap/scss/bootstrap.scss';

// Global registration
Vue.component('vue-bootstrap-autocomplete', VueBootstrapAutocomplete)

// or

// Local Registration
export default {
  components: {
    VueBootstrapAutocomplete
  }
}
```

## Basic Usage
The only required props are a `v-model` and a `data` array.

```html
<vue-bootstrap-autocomplete
  v-model="query"
  :data="['Canada', 'United States', 'Mexico', 'Netherlands']"
/>
```

## Nuxt.js

Configure the [build transpile](https://nuxtjs.org/api/configuration-build/#transpile) option in `nuxt.config.js`.
```js
{
  build: {
    transpile: [
      ({ isServer }) => 'vue-bootstrap-autocomplete'
    ]
  }
}
```
