var earning = document.getElementById("earning").getContext("2d");

// Fetch data from the API
fetch("get_chart_data.php")
  .then((response) => response.json())
  .then((data) => {
    // Process the data
    const positions = {};
    data.forEach((item) => {
      const position = item.position;
      if (positions[position]) {
        positions[position]++;
      } else {
        positions[position] = 1;
      }
    });

    // Prepare chart data
    const labels = Object.keys(positions);
    const chartData = Object.values(positions);

    // Create the chart
    new Chart(earning, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "User Positions",
            data: chartData,
            backgroundColor: [
              "rgba(255, 99, 132, 0.2)",
              "rgba(54, 162, 235, 0.2)",
              "rgba(255, 206, 86, 0.2)",
              "rgba(75, 192, 192, 0.2)",
              "rgba(153, 102, 255, 0.2)",
              "rgba(255, 159, 64, 0.2)",
            ],
            borderColor: [
              "rgba(255, 99, 132, 1)",
              "rgba(54, 162, 235, 1)",
              "rgba(255, 206, 86, 1)",
              "rgba(75, 192, 192, 1)",
              "rgba(153, 102, 255, 1)",
              "rgba(255, 159, 64, 1)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  })
  .catch((error) => console.error("Error fetching chart data:", error));