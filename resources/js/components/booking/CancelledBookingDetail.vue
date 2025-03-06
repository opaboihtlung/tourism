<template>
  <div class="container px-8 mt-10">
    <button class="mt-10 ml-5" onclick="history.back()">Back</button>
    <div v-if="loading">
      Loading
      <div class="w-8 h-8 spinner-border animate-spin" role="status">
        <span class="visually-hidden">.</span>
      </div>
    </div>
    <div v-else>
      <div class="mx-2 mb-6 text-2xl font-semibold text-black">{{ id }}</div>
      <div class="flex flex-col mt-8">
        <div
          class="py-2 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8"
        >
          <div
            class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg"
          >
            <div class="container p-4 mx-auto bg-white">
              <div class="text-xl">Transaction Details</div>
              <hr />
              <table class="w-full">
                <tr>
                  <td>Date</td>
                  <td>{{ details.txnDate }}</td>
                </tr>
                <tr>
                  <td>Status</td>
                  <td v-if="refundAmount > 0">REFUNDED</td>
                  <td v-else>
                    {{ resultInfo.resultStatus }}
                  </td>
                </tr>
                <tr>
                  <td>Amount</td>
                  <td>{{ details.txnAmount }}</td>
                </tr>
              </table>
              <br />
              <!-- <button @click="initiateRefund" v-if='resultInfo.resultStatus == "TXN_SUCCESS"'
                class="p-2 pl-4 pr-4 text-sm text-white bg-green-600 rounded-full hover:bg-green-500">Refund</button> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";
export default {
  data() {
    return {
      id: null,
      details: [],
      loading: false,
      resultInfo: [],
      refundAmount: 0,
    };
  },
  mounted() {
    this.id = this.$route.params.id;
    this.checkTransaction();
  },
  methods: {
    checkTransaction() {
      this.loading = true;
      axios
        .get("/api/check-status?orderId=" + this.id)
        .then((res) => {
          this.details = res.data.response.response.body;
          this.resultInfo = this.details.resultInfo;
          this.loading = false;
          this.refundAmount = res.data.response.response.body.refundAmt;
          if (this.refundAmount > 0) {
            this.updatePayment("REFUNDED");
          } else {
            this.updatePayment(this.resultInfo.resultStatus);
          }
        })
        .catch((err) => {
          this.loading = false;
          throw err;
        });
    },
    updatePayment(status) {
      this.loading = true;
      axios
        .post("/api/initiate-refund", {
          orderId: this.id,
          status: status,
        })
        .then((res) => {
          this.loading = false;
        })
        .catch((err) => {
          this.loading = false;
          throw err;
        });
    },
  },
};
</script>
