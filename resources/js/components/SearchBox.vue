<template>
    <div>
     OK

     <vue-bootstrap-autocomplete 
        class="col-md-3 col-sm-3"
        placeholder="vue search"
        v-model="state.query" 
        :data="state.results"
        :serializer="mySerializer"

     />
    </div>
</template>


<script setup>

import { reactive, onMounted, computed, watch} from 'vue'
import { debounce } from "lodash"


const state = reactive({ query : '', results : []})


const mySerializer = computed((s) => 
  //  if(typeof s != 'object')
   //     return s
    function(s)  {

        return s.display_name

   }
)

function getResults()
{

    axios.get('/pos/customer/search?q=' + state.query)
        .then(response => {
                state.results = response.data
        })
}

watch(
    () => state.query, 
        debounce(function(q) {
                if(q.length > 2)
                    getResults()
                }, 500)
)


</script>
<style scoped>

</style>