/* global Chart:false */

(function () {
  "use strict";

  // simpan instance agar bisa di-destroy saat navigasi
  const registry = {};

  function makeChart(id, config) {
    const el = document.getElementById(id);
    if (!el || !(el instanceof HTMLCanvasElement)) return; // guard halaman lain

    // destroy instance lama (kalau ada)
    if (registry[id]) {
      try {
        registry[id].destroy();
      } catch (_) {}
      registry[id] = null;
    }

    const ctx = el.getContext("2d");
    if (!ctx) return;

    registry[id] = new Chart(ctx, config);
  }

  function initCharts() {
    const ticksStyle = {
      fontColor: "#495057",
      fontStyle: "bold",
    };
    const mode = "index";
    const intersect = true;

    // SALES
    makeChart("sales-chart", {
      type: "bar",
      data: {
        labels: ["JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"],
        datasets: [
          {
            backgroundColor: "#007bff",
            borderColor: "#007bff",
            data: [1000, 2000, 3000, 2500, 2700, 2500, 3000],
          },
          {
            backgroundColor: "#ced4da",
            borderColor: "#ced4da",
            data: [700, 1700, 2700, 2000, 1800, 1500, 2000],
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        tooltips: { mode, intersect },
        hover: { mode, intersect },
        legend: { display: false },
        scales: {
          yAxes: [
            {
              gridLines: {
                display: true,
                lineWidth: "4px",
                color: "rgba(0,0,0,.2)",
                zeroLineColor: "transparent",
              },
              ticks: Object.assign(
                {
                  beginAtZero: true,
                  callback: function (value) {
                    if (value >= 1000) {
                      value = value / 1000 + "k";
                    }
                    return "$" + value;
                  },
                },
                ticksStyle
              ),
            },
          ],
          xAxes: [
            {
              display: true,
              gridLines: { display: false },
              ticks: ticksStyle,
            },
          ],
        },
      },
    });

    // VISITORS
    makeChart("visitors-chart", {
      data: {
        labels: ["18th", "20th", "22nd", "24th", "26th", "28th", "30th"],
        datasets: [
          {
            type: "line",
            data: [100, 120, 170, 167, 180, 177, 160],
            backgroundColor: "transparent",
            borderColor: "#007bff",
            pointBorderColor: "#007bff",
            pointBackgroundColor: "#007bff",
            fill: false,
          },
          {
            type: "line",
            data: [60, 80, 70, 67, 80, 77, 100],
            backgroundColor: "transparent",
            borderColor: "#ced4da",
            pointBorderColor: "#ced4da",
            pointBackgroundColor: "#ced4da",
            fill: false,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        tooltips: { mode, intersect },
        hover: { mode, intersect },
        legend: { display: false },
        scales: {
          yAxes: [
            {
              gridLines: {
                display: true,
                lineWidth: "4px",
                color: "rgba(0,0,0,.2)",
                zeroLineColor: "transparent",
              },
              ticks: Object.assign(
                {
                  beginAtZero: true,
                  suggestedMax: 200,
                },
                ticksStyle
              ),
            },
          ],
          xAxes: [
            {
              display: true,
              gridLines: { display: false },
              ticks: ticksStyle,
            },
          ],
        },
      },
    });
  }

  // pertama kali halaman siap
  document.addEventListener("DOMContentLoaded", initCharts);
  // setiap selesai pindah halaman dengan Livewire v3
  document.addEventListener("livewire:navigated", initCharts);
})();
