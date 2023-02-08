# Examples

## Basic

<HomePageDemo/>

```vue
<template>
  <div class="pl-1 pb-2 pt-3">Selected Country: {{ query }}</div>
  <div>
    Options: `Canada, United States, Mexico, Netherlands`,
    <vue-bootstrap-autocomplete
      :data="['Canada', 'United States', 'Mexico', 'Netherlands']"
      v-model="query"
      placeholder="Choose a country"
    />
  </div>
</template>

<script>
export default {
  data() {
    return {
      query: "",
    };
  },
};
</script>
```

## API Example

<APIExample/>

```vue
<template>
  <div>
    <div class="pl-1 pb-2 pt-3">
      Selected user: <span v-if="selecteduser">{{ selecteduser.login }}</span>
    </div>
    <vue-bootstrap-autocomplete
      class="mb-4"
      v-model="query"
      :ieCloseFix="false"
      :data="users"
      :serializer="(item) => item.login"
      @hit="selecteduser = $event"
      :disabledValues="selecteduser ? [selecteduser.login] : []"
      placeholder="Search Github Users"
      @input="lookupUser"
      #use a different background color for even or odd user ids
      :background-variant-resolver="
        (user) => (user.id % 2 == 0 ? 'light' : 'dark')
      "
    />
  </div>
</template>

<script>
import { debounce } from "lodash";

export default {
  data() {
    return {
      query: "",
      selecteduser: null,
      users: [],
    };
  },

  methods: {
    lookupUser: debounce(function () {
      // in practice this action should be debounced
      fetch(`https://api.github.com/search/users?q=${this.query}`)
        .then((response) => {
          return response.json();
        })
        .then((data) => {
          this.users = data.items;
        });
    }, 500),
  },
};
</script>
```

## Prepend/Append

<PendingAppendingExample/>

```vue
<template>
  <div>
    <VueBootstrapAutocomplete
      class="mb-4"
      v-model="query"
      :data="users"
      @keydown.enter="search"
      :serializer="(item) => item.login"
      placeholder="Search GitHub Users"
      prepend="Username:"
      @input="searchUsers"
      @keyup.enter="handleEnter"
    >
      <template slot="append">
        <button class="btn btn-primary">Search</button>
      </template>
    </VueBootstrapAutocomplete>
  </div>
</template>

<script>
import { debounce } from "lodash";
export default {
  data() {
    return {
      query: "",
      userRepositories: {},
      users: [],
    };
  },

  methods: {
    handleEnter: function (event) {
      alert("keyup.enter pressed!");
    },
    searchUsers: debounce(function () {
      fetch(`https://api.github.com/search/users?q=${this.query}`)
        .then((response) => {
          return response.json();
        })
        .then((data) => {
          this.users = data.items;
        });
    }, 500),
  },
};
</script>
```

## Custom Suggestion Slot

<CustomSuggestion/>

```vue
<template>
  <div>
    <div class="pl-1 pb-2 pt-3">
      Selected user: <span v-if="selecteduser">{{ selecteduser.login }}</span>
    </div>
    <vue-bootstrap-autocomplete
      class="mb-4"
      v-model="query"
      :data="users"
      :serializer="(item) => item.login"
      :screen-reader-text-serializer="(item) => `Github user ${item.login}`"
      highlightClass="special-highlight-class"
      @hit="selecteduser = $event"
      :minMatchingChars="3"
      placeholder="Search Github Users"
      inputClass="special-input-class"
      :disabledValues="selecteduser ? [selecteduser.login] : []"
      @input="lookupUser"
    >
      <template slot="suggestion" slot-scope="{ data, htmlText }">
        <div class="d-flex align-items-center">
          <img
            class="rounded-circle"
            :src="data.avatar_url"
            style="width: 40px; height: 40px;"
          />

          <!-- Note: the v-html binding is used, as htmlText contains
               the suggestion text highlighted with <strong> tags -->
          <span class="ml-4" v-html="htmlText"></span>
          <i class="ml-auto fab fa-github-square fa-2x"></i>
        </div>
      </template>
    </vue-bootstrap-autocomplete>
  </div>
</template>

<script>
import { debounce } from "lodash";
export default {
  data() {
    return {
      query: "",
      selecteduser: null,
      users: [],
    };
  },

  methods: {
    lookupUser: debounce(function () {
      // in practice this action should be debounced
      fetch(`https://api.github.com/search/users?q=${this.query}`)
        .then((response) => {
          return response.json();
        })
        .then((data) => {
          this.users = data.items;
        });
    }, 500),
  },
};
</script>

<style lang="scss">
@import "bootstrap/scss/bootstrap.scss";

.special-input-class {
  background-color: black !important;
  color: white !important;
}
.special-highlight-class {
  font-weight: 900;
  color: #585656;
}
</style>
```

## Events

<EventsDemo/>

```vue
<template>
  <div>
    <div class="pl-1 pb-2 pt-3">Selected Country: {{ query }}</div>
    <vue-bootstrap-autocomplete
      :data="[
        'Canada',
        'United Kingdom',
        'United States',
        'Mexico',
        'Netherlands',
      ]"
      v-model="query"
      showOnFocus
      placeholder="Choose a country"
      @blur="() => onEvent('blur')"
      @focus="() => onEvent('focus')"
      @hit="() => onEvent('hit')"
      @input="() => onEvent('input')"
      @keyup="() => onEvent('keyup')"
      @paste="() => onEvent('paste')"
    />
  </div>
</template>

<script>
import "bootstrap-vue/dist/bootstrap-vue.css";
import VueBootstrapAutocomplete from "../../../src/components/VueBootstrapAutocomplete";

export default {
  name: "EventsDemo",
  components: { VueBootstrapAutocomplete },
  data() {
    return {
      query: "",
    };
  },
  methods: {
    onEvent(event) {
      this.$bvToast.toast(event, {
        title: "Event emitted",
        autoHideDelay: 5000,
        variant: "info",
        solid: true,
      });
    },
  },
};
</script>
```

## No Results Info Slot

<NoResultsInfoDemo/>

You can either use the `noResultsInfo` prop to display a message when there are no results, or you can use the `no-results-info` slot to display a custom message.

```vue
<template>
  <div>
    <div class="pl-1 pb-2 pt-3">Selected Country: {{ query }}</div>

    <h2>Using the <code>noResultsInfo</code> slot</h2>
    <vue-bootstrap-autocomplete
      class="mb-4"
      :data="[
        'Canada',
        'United Kingdom',
        'United States',
        'Mexico',
        'Netherlands',
      ]"
      v-model="query"
      showOnFocus
      placeholder="Choose a country"
    >
      <template slot="noResultsInfo">
        <span>{{ noResultsInfo }} <b>{{ query }}</b></span>
      </template>
    </vue-bootstrap-autocomplete>

    <h2>Using the <code>noResultsInfo</code> prop</h2>
    <vue-bootstrap-autocomplete
      class="mb-4"
      :data="[
        'Canada',
        'United Kingdom',
        'United States',
        'Mexico',
        'Netherlands',
      ]"
      v-model="query"
      showOnFocus
      placeholder="Choose a country"
      no-results-info="No results found (using prop)"
    />
  </div>
</template>

<script>
import "bootstrap-vue/dist/bootstrap-vue.css";
import VueBootstrapAutocomplete from "../../../src/components/VueBootstrapAutocomplete";

export default {
  name: "NoResultsInfoDemo",
  components: { VueBootstrapAutocomplete },
  data() {
    return {
      query: "",
    };
  },
  computed: {
    noResultsInfo() {
      return `No results found for`;
    },
  },
};
</script>
```
