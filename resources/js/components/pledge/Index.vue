<template>
  <div class="container px-8 mt-10">
    <div class="mx-2 mb-6 text-2xl font-semibold text-black">
      Pledge

    </div>


    <div class="flex flex-col mt-8">
      <div class="py-2 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg">
          <table class="min-w-full">
            <thead>
              <tr>
                <th
                  class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                  Sl No
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                  Name
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                  Pledge On
                </th>


              </tr>
            </thead>

            <div v-show="hasLoading" class="flex items-center justify-center">
              Loading
              <div class="w-8 h-8 spinner-border animate-spin " role="status">
                <span class="visually-hidden">.</span>
              </div>
            </div>


            <tbody class="bg-white">
              <tr v-for="(value, index) in data" :key="value.name">
                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                  <div class="flex items-center">
                    {{ fromData + index }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                  <div class="flex items-center">
                    {{ value.user.name }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                  <div class="flex items-center">
                    {{ format_date(value.created_at) }}
                  </div>
                </td>


              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- Pagination Style -->
  <div class="flex items-center justify-between px-4 py-3 mx-8 bg-white border-t border-gray-200 sm:px-8">
    <div class="sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Showing
          <span class="font-medium">{{ fromData }}</span>
          to
          <span class="font-medium">{{ toData }}</span>
          of
          <span class="font-medium">{{ total }}</span>
          results
        </p>
      </div>
      <div>
        <nav class="inline-flex -space-x-px rounded-md shadow-sm isolate" aria-label="Pagination">
          <div v-for="(page, index) in pages">
            <div v-if="page['active']">
              <button @click="changePage(page['url'])" aria-current="page"
                class="relative z-10 inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-green-600 focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                {{ page["label"] }}
              </button>
            </div>
            <div v-else>
              <button @click="changePage(page['url'])"
                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                :disabled="!page['url']">
                <div v-if="page['label'].includes('Previous')">
                  Previous
                </div>
                <div v-else-if="page['label'].includes('Next')">
                  Next
                </div>
                <div v-else>
                  {{ page['label'] }}
                </div>
              </button>
            </div>
          </div>
        </nav>
      </div>
    </div>
  </div>
  <!-- Pagination ends -->
</template>

<script>
import axios from "axios";
import moment from 'moment';

export default {
  data() {
    return {
      data: null,
      hasLoading: true,
      previousLink: '',
      nextLink: '',
      fromData: 0,
      toData: 0,
      total: 0,
      pages: null,
    };
  },
  created() {
    this.changePage("/api/pledges")
    // axios.get("/api/pledges").then((result) => {
    //   console.log(result.data);
    //   this.data = result.data.data;
    //   this.hasLoading=false
    // });
    // store.commit('role','he man')
  },
  methods: {
    format_date(value) {
      if (value) {
        return moment(String(value)).format('DD-MM-YYYY')
      }
    },
    changePage(url) {
      if (this.searched == true) {
        this.continueSearch(url);
      } else {
        axios.get(url)
          .then((result) => {
            if (result.data.data == false) {
              this.hasLoading = false
            } else {
              this.data = result.data.data.data;
              this.hasLoading = false;
              this.nextLink = result.data.data.next_page_url;
              this.previousLink = result.data.data.prev_page_url;
              this.fromData = result.data.data.from;
              this.toData = result.data.data.to;
              this.total = result.data.data.total;
              this.pages = result.data.data.links;
            }
          })
      }
    },

  },
  computed: {
    getterRole() {
      return this.$store.getters.getRole;
    }
  }
};
</script>
