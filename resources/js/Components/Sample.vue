<template>
    <div>
      <h1>Sample Data</h1>
      <table border="1" class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Email</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in sampleData" :key="item.id">
            <td>{{ item.id }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.age }}</td>
            <td>{{ item.email }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        sampleData: [], // Array to store fetched data
      };
    },
    mounted() {
      this.fetchData(); // Fetch data when the component is mounted
    },
    methods: {
      async fetchData() {
        try {
          const response = await fetch('/api/sample-data'); // Fetch data from Laravel API
          const data = await response.json(); // Parse JSON response
          this.sampleData = data; // Assign data to the sampleData array
        } catch (error) {
          console.error('Error fetching data:', error);
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }
  .data-table th,
  .data-table td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
  }
  .data-table th {
    background-color: #f4f4f4;
  }
  </style>