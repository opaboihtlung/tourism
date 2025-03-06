<template>
  <div>
    <div class="chart-title">
      <p class="chart-title">Select Year</p>
      <input
        type="number"
        v-model="selectedYear"
        placeholder="Enter Year"
        @change="fetchData"
      />
      <button class="custom-button" @click="fetchData">Submit</button>
    </div>
    <div class="chart-section">
      <div class="chart-container bg-white">
        <p class="chart-title">Monthly Bookings for {{ selectedYear }}</p>
        <apexchart
          :options="chartOptions"
          :series="bookingsSeries"
          type="bar"
          height="350"
        />
      </div>
      <div class="chart-container bg-white">
        <p class="chart-title">Monthly New Users for {{ selectedYear }}</p>
        <apexchart
          :options="chartOptions"
          :series="usersSeries"
          type="bar"
          height="350"
        />
      </div>
    </div>
  </div>
</template>

<style>
.chart-section {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-around;
}

.chart-container {
  width: 33rem; /* Adjust width as needed */
  margin: 10px;
  /* Change color as needed */
  /* border-radius: 20; */
  border: 1.5px solid #ccc;
  border-radius: 18px;
}
.chart-title {
  text-align: center; /* Center align the text */
  font-weight: bold;
  padding: 15px;
  color: #4caf50; /* Make the text bold */
}
.chart-title {
  text-align: center; /* Center align the text */
}

input[type="number"] {
  padding: 10px; /* Add padding to the input */
  border: 1px solid #ccc; /* Add a border */
  border-radius: 5px; /* Add border-radius for rounded corners */
  margin-right: 10px; /* Add spacing between input and button */
}

.custom-button {
  background-color: #4caf50; /* Green background */
  border: none; /* Remove border */
  color: white; /* White text color */
  padding: 10px 20px; /* Add padding */
  text-align: center; /* Center align text */
  text-decoration: none; /* Remove underline */
  display: inline-block; /* Make the button inline */
  font-size: 16px; /* Set font size */
  border-radius: 5px; /* Add border-radius for rounded corners */
  cursor: pointer; /* Add pointer cursor on hover */
}

.custom-button:hover {
  background-color: #45a049; /* Darker green on hover */
}
</style>

<script>
import VueApexCharts from "vue3-apexcharts";

export default {
  components: {
    apexchart: VueApexCharts,
  },
  data() {
    return {
      chartOptions: {
        chart: {
          id: "bar",
        },

        xaxis: {
          categories: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
          ],
        },

        stroke: {
          curve: "stepline", // Set curve to 'stepline'
        },
      },
      selectedYear: new Date().getFullYear(),
      bookingsSeries: [],
      usersSeries: [],
      // visitorsSeries: [],
    };
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      axios
        .get(`/api/bookingsMonthly?year=${this.selectedYear}`)
        .then((result) => {
          const data = result.data.data;

          // Initialize an array to hold visitor counts for all months
          const monthlybookingsCounts = Array.from({ length: 12 }, () => 0);

          // Update visitor counts for existing months
          data.forEach((item) => {
            monthlybookingsCounts[item.month - 1] = item.total_bookings;
          });

          const series = [
            {
              name: "Total Users",
              data: monthlybookingsCounts,
            },
          ];

          this.bookingsSeries = series;
        })
        .catch((error) => {
          console.error("Error fetching bookings data from server:", error);
        });

      axios
        .get(`/api/getUsersMonthly?year=${this.selectedYear}`)
        .then((result) => {
          const data = result.data.data;

          // Initialize an array to hold visitor counts for all months
          const monthlyUsersCounts = Array.from({ length: 12 }, () => 0);

          // Update visitor counts for existing months
          data.forEach((item) => {
            monthlyUsersCounts[item.month - 1] = item.total_users;
          });

          const series = [
            {
              name: "Total Users",
              data: monthlyUsersCounts,
            },
          ];

          this.usersSeries = series;
        })
        .catch((error) => {
          console.error("Error fetching user data from server:", error);
        });
    },
    getMonthName(monthNumber) {
      const months = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ];
      return months[monthNumber - 1];
    },
  },
};
</script>

<style>
.apex-chart-container {
  width: 800px;
  height: 400px;
}
</style>
