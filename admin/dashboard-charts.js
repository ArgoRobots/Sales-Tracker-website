document.addEventListener("DOMContentLoaded", function () {
  const rawData = window.dashboardData;
  const isGeoEnabled = rawData.geoLocationEnabled || false;
  const privacySettings = rawData.privacySettings || {};

  const typeColors = {
    ExcelSheetsChart: "#3b82f6",
    GoogleSheetsChart: "#10b981",
    Backup: "#f59e0b",
    XLSX: "#ef4444",
    Receipts: "#8b5cf6",
  };

  const countryColors = {
    "United States": "#3b82f6",
    Canada: "#ef4444",
    "United Kingdom": "#10b981",
    Germany: "#f59e0b",
    Australia: "#8b5cf6",
    France: "#06b6d4",
    Netherlands: "#84cc16",
    Japan: "#f97316",
    Brazil: "#ec4899",
    India: "#6366f1",
  };

  if (!rawData.dataPoints) {
    console.log("No data points available");
    return;
  }

  const exportData = rawData.dataPoints.Export || [];
  const openaiData = rawData.dataPoints.OpenAI || [];
  const exchangeRatesData = rawData.dataPoints.OpenExchangeRates || [];
  const googleSheetsData = rawData.dataPoints.GoogleSheets || [];
  const sessionData = rawData.dataPoints.Session || [];
  const errorData = rawData.dataPoints.Error || [];

  console.log("Data loaded:", {
    exports: exportData.length,
    openai: openaiData.length,
    exchangeRates: exchangeRatesData.length,
    googleSheets: googleSheetsData.length,
    sessions: sessionData.length,
    errors: errorData.length,
    geoEnabled: isGeoEnabled,
  });

  // Initialize all charts
  generateStatistics(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  );

  if (isGeoEnabled) {
    generateCountryDistributionChart(
      exportData,
      openaiData,
      exchangeRatesData,
      googleSheetsData,
      sessionData,
      errorData
    );
    generateCityDistributionChart(
      exportData,
      openaiData,
      exchangeRatesData,
      googleSheetsData,
      sessionData,
      errorData
    );
    generateFeatureUsageByRegionChart(
      exportData,
      openaiData,
      exchangeRatesData
    );
    generatePerformanceByCountryChart(
      exportData,
      openaiData,
      exchangeRatesData
    );
    generateErrorRatesByCountryChart(
      errorData,
      exportData,
      openaiData,
      exchangeRatesData
    );
    generateSessionDurationByRegionChart(sessionData);
    generateTimezoneChart(
      exportData,
      openaiData,
      exchangeRatesData,
      googleSheetsData,
      sessionData,
      errorData
    );
  }

  generateVersionDistributionChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  );
  generateVersionTimeChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  );
  generateTopVersionsChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  );
  generateVersionPerformanceChart(exportData, openaiData, exchangeRatesData);
  generateVersionSessionChart(sessionData);
  generateVersionErrorChart(
    errorData,
    exportData,
    openaiData,
    exchangeRatesData,
    sessionData
  );

  generateErrorCategoryChart(errorData);
  generateErrorCodeChart(errorData);
  generateErrorTimeChart(errorData);
  generateStabilityChart(
    exportData,
    openaiData,
    exchangeRatesData,
    sessionData,
    errorData
  );

  generateSessionDurationChart(sessionData);
  generateExportTypesBreakdown(exportData);
  generateExportDurationByTypeChart(exportData);
  generateExportFileSizeByTypeChart(exportData);

  generateExportDurationChart(exportData);
  generateOpenAIChart(openaiData);
  generateOpenAITokenChart(openaiData);
  generateExchangeRatesChart(exchangeRatesData);

  generateExportFileSizeChart(exportData);
  generateOverallActivityChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  );

  // Enhanced Statistics Function with More Useful Metrics
  function generateStatistics(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  ) {
    const statsGrid = document.getElementById("statsGrid");

    const totalOperations =
      exportData.length +
      openaiData.length +
      exchangeRatesData.length +
      googleSheetsData.length;
    const totalErrors = errorData.length;
    const totalSessions = sessionData.length;

    // Calculate Performance Score (0-100 based on average response times)
    const allDurations = [
      ...exportData.map((d) => parseFloat(d.DurationMS || 0)),
      ...openaiData.map((d) => parseFloat(d.DurationMS || 0)),
      ...exchangeRatesData.map((d) => parseFloat(d.DurationMS || 0)),
    ].filter((d) => d > 0);

    const avgDuration =
      allDurations.length > 0
        ? allDurations.reduce((sum, d) => sum + d, 0) / allDurations.length
        : 0;

    // Performance score: 100 = excellent (< 1000ms), decreasing as duration increases
    const performanceScore = Math.max(
      0,
      Math.min(100, Math.round(100 - (avgDuration - 1000) / 50))
    );

    // Calculate System Health Score (based on error rate, performance, and data completeness)
    const errorRate =
      totalOperations > 0 ? (totalErrors / totalOperations) * 100 : 0;
    const healthScore = Math.max(
      0,
      Math.round(100 - errorRate * 10 - (avgDuration > 2000 ? 20 : 0))
    );

    // Find Most Popular Feature
    const featureUsage = {
      Export: exportData.length,
      "AI Assistant": openaiData.length,
      "Currency Rates": exchangeRatesData.length,
      "Google Sheets": googleSheetsData.length,
    };
    const mostUsedFeature = Object.entries(featureUsage).sort(
      ([, a], [, b]) => b - a
    )[0];

    // Calculate Peak Usage Hour
    const allData = [
      ...exportData,
      ...openaiData,
      ...exchangeRatesData,
      ...googleSheetsData,
      ...sessionData,
    ];
    const hourCounts = {};
    allData.forEach((item) => {
      if (item.timestamp) {
        const hour = new Date(item.timestamp).getHours();
        hourCounts[hour] = (hourCounts[hour] || 0) + 1;
      }
    });
    const peakHour = Object.entries(hourCounts).sort(
      ([, a], [, b]) => b - a
    )[0];
    const peakHourText = peakHour ? `${peakHour[0]}:00` : "N/A";

    // Calculate Average Session Duration
    const sessionEndData = sessionData.filter(
      (s) => s.action === "SessionEnd" && s.duration > 0
    );
    const avgSessionDuration =
      sessionEndData.length > 0
        ? (
            sessionEndData.reduce(
              (sum, s) => sum + parseFloat(s.duration),
              0
            ) / sessionEndData.length
          ).toFixed(1)
        : "0";

    // Calculate Unique Countries
    const uniqueCountries = new Set(
      allData.map((d) => d.country).filter((c) => c && c !== "Unknown")
    ).size;

    // Calculate Data Quality Score
    let dataQualityScore = 100;
    const totalDataPoints = totalOperations + totalSessions + totalErrors;

    if (totalDataPoints > 0) {
      // Penalize for missing data
      const missingDataRate =
        allData.filter((d) => !d.country || d.country === "Unknown").length /
        totalDataPoints;
      dataQualityScore -= missingDataRate * 30;

      // Penalize for errors
      dataQualityScore -= errorRate * 2;

      // Penalize for inconsistent timestamps
      const invalidTimestamps = allData.filter(
        (d) => !d.timestamp || isNaN(new Date(d.timestamp).getTime())
      ).length;
      dataQualityScore -= (invalidTimestamps / totalDataPoints) * 20;
    }
    dataQualityScore = Math.max(0, Math.round(dataQualityScore));

    // Calculate Active Users Today (based on unique sessions)
    const today = new Date().toDateString();
    const todaysSessions = sessionData.filter((s) => {
      const sessionDate = new Date(s.timestamp).toDateString();
      return sessionDate === today;
    });
    const activeUsersToday = new Set(todaysSessions.map((s) => s.hashedIP))
      .size;

    // Calculate User Retention as percentage of returning sessions
    const uniqueUsers = new Set(sessionData.map((s) => s.hashedIP)).size;
    const userRetentionRate =
      uniqueUsers > 0
        ? (((totalSessions - uniqueUsers) / uniqueUsers) * 100).toFixed(1)
        : "0";

    // Version Adoption Rate (percentage using latest version)
    const allDataWithVersion = [
      ...exportData,
      ...openaiData,
      ...exchangeRatesData,
      ...googleSheetsData,
      ...sessionData,
      ...errorData,
    ];
    const versionCounts = {};
    allDataWithVersion.forEach((item) => {
      if (item.appVersion) {
        versionCounts[item.appVersion] =
          (versionCounts[item.appVersion] || 0) + 1;
      }
    });

    const latestVersion = Object.keys(versionCounts).sort(
      (a, b) =>
        parseFloat(b.replace(/[^\d.]/g, "")) -
        parseFloat(a.replace(/[^\d.]/g, ""))
    )[0];
    const latestVersionUsage = latestVersion ? versionCounts[latestVersion] : 0;
    const adoptionRate =
      allDataWithVersion.length > 0
        ? Math.round((latestVersionUsage / allDataWithVersion.length) * 100)
        : 0;

    const stats = [
      {
        title: "System Health",
        value: `${healthScore}%`,
        subtext:
          healthScore > 90
            ? "Excellent"
            : healthScore > 70
            ? "Good"
            : "Needs attention",
      },
      {
        title: "Performance Score",
        value: `${performanceScore}%`,
        subtext: `${Math.round(avgDuration)}ms avg response`,
      },
      {
        title: "Data Quality",
        value: `${dataQualityScore}%`,
        subtext: "Completeness & accuracy",
      },
      {
        title: "Active Users Today",
        value: activeUsersToday.toString(),
        subtext: "Unique sessions today",
      },
      {
        title: "Most Used Feature",
        value: mostUsedFeature ? mostUsedFeature[0] : "N/A",
        subtext: mostUsedFeature ? `${mostUsedFeature[1]} uses` : "No data",
      },
      {
        title: "Peak Usage Time",
        value: peakHourText,
        subtext: peakHour ? `${peakHour[1]} operations` : "No data",
      },
      {
        title: "Avg Session Duration",
        value: avgSessionDuration + "s",
        subtext:
          sessionEndData.length > 0
            ? `${sessionEndData.length} sessions`
            : "No data",
      },
      {
        title: "Unique Countries",
        value: uniqueCountries.toString(),
        subtext: uniqueCountries > 0 ? "geo-distribution" : "No data",
      },
      {
        title: "User Retention",
        value: userRetentionRate + "%",
        subtext: `${uniqueUsers} unique users`,
      },
      {
        title: "Version Adoption",
        value: `${adoptionRate}%`,
        subtext: latestVersion ? `Using v${latestVersion}` : "No data",
      },
      {
        title: "Total Operations",
        value: totalOperations.toLocaleString(),
        subtext: "All user actions",
      },
      {
        title: "Error Rate",
        value: `${errorRate.toFixed(2)}%`,
        subtext: totalErrors > 0 ? `${totalErrors} incidents` : "No errors",
      },
    ];

    statsGrid.innerHTML = stats
      .map(
        (stat) => `
            <div class="stat-card">
                <h3>${stat.title}</h3>
                <div class="value">${stat.value}</div>
                ${
                  stat.subtext
                    ? `<div class="subtext">${stat.subtext}</div>`
                    : ""
                }
            </div>
        `
      )
      .join("");
  }

  // Helper function to format file sizes
  function formatFileSize(bytes) {
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + "MB";
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + "KB";
    return bytes + "B";
  }

  // Geographic Charts
  function generateCountryDistributionChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  ) {
    const allData = [
      ...exportData,
      ...openaiData,
      ...exchangeRatesData,
      ...googleSheetsData,
      ...sessionData,
      ...errorData,
    ];
    const countryCounts = {};

    allData.forEach((item) => {
      const country = item.country || "Unknown";
      if (country !== "Unknown") {
        countryCounts[country] = (countryCounts[country] || 0) + 1;
      }
    });

    if (Object.keys(countryCounts).length === 0) {
      document.getElementById(
        "countryDistributionChart"
      ).parentElement.innerHTML =
        '<div class="chart-no-data">No country data available</div>';
      return;
    }

    const sortedCountries = Object.entries(countryCounts)
      .sort(([, a], [, b]) => b - a)
      .slice(0, 15);

    const labels = sortedCountries.map(([country]) => country);
    const data = sortedCountries.map(([, count]) => count);
    const colors = labels.map((country) => countryColors[country] || "#9ca3af");

    new Chart(document.getElementById("countryDistributionChart"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Operations",
            data: data,
            backgroundColor: colors,
            borderColor: colors.map((c) => c.replace("0.8", "1")),
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: "y",
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          x: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Number of Operations",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateCityDistributionChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  ) {
    if (!privacySettings.collectCityData) {
      document.getElementById("cityDistributionChart").parentElement.innerHTML =
        '<div class="chart-no-data">City data collection disabled for privacy</div>';
      return;
    }

    const allData = [
      ...exportData,
      ...openaiData,
      ...exchangeRatesData,
      ...googleSheetsData,
      ...sessionData,
      ...errorData,
    ];
    const cityCounts = {};

    allData.forEach((item) => {
      const city = item.city || "Unknown";
      if (city !== "Unknown" && city !== "Hidden") {
        const region = item.region || "";
        const country = item.country || "";
        const fullLocation = region
          ? `${city}, ${region}, ${country}`
          : `${city}, ${country}`;
        cityCounts[fullLocation] = (cityCounts[fullLocation] || 0) + 1;
      }
    });

    if (Object.keys(cityCounts).length === 0) {
      document.getElementById("cityDistributionChart").parentElement.innerHTML =
        '<div class="chart-no-data">No city data available</div>';
      return;
    }

    const sortedCities = Object.entries(cityCounts)
      .sort(([, a], [, b]) => b - a)
      .slice(0, 10);

    new Chart(document.getElementById("cityDistributionChart"), {
      type: "pie",
      data: {
        labels: sortedCities.map(([city]) => city),
        datasets: [
          {
            data: sortedCities.map(([, count]) => count),
            backgroundColor: [
              "#3b82f6",
              "#10b981",
              "#f59e0b",
              "#ef4444",
              "#8b5cf6",
              "#06b6d4",
              "#84cc16",
              "#f97316",
              "#ec4899",
              "#6366f1",
            ],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((context.raw / total) * 100);
                return `${context.label}: ${context.raw} (${percentage}%)`;
              },
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateFeatureUsageByRegionChart(
    exportData,
    openaiData,
    exchangeRatesData
  ) {
    const regionData = {};

    const addToRegion = (item, type) => {
      const country = item.country || "Unknown";
      if (country !== "Unknown") {
        if (!regionData[country]) {
          regionData[country] = {
            exports: 0,
            openai: 0,
            exchangeRates: 0,
          };
        }
        regionData[country][type]++;
      }
    };

    exportData.forEach((item) => addToRegion(item, "exports"));
    openaiData.forEach((item) => addToRegion(item, "openai"));
    exchangeRatesData.forEach((item) => addToRegion(item, "exchangeRates"));

    if (Object.keys(regionData).length === 0) {
      document.getElementById(
        "featureUsageByRegionChart"
      ).parentElement.innerHTML =
        '<div class="chart-no-data">No regional feature usage data available</div>';
      return;
    }

    const countries = Object.keys(regionData).slice(0, 10);

    new Chart(document.getElementById("featureUsageByRegionChart"), {
      type: "bar",
      data: {
        labels: countries,
        datasets: [
          {
            label: "Exports",
            data: countries.map((country) => regionData[country].exports),
            backgroundColor: "#3b82f6",
          },
          {
            label: "OpenAI",
            data: countries.map((country) => regionData[country].openai),
            backgroundColor: "#8b5cf6",
          },
          {
            label: "Exchange Rates",
            data: countries.map((country) => regionData[country].exchangeRates),
            backgroundColor: "#f59e0b",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            stacked: true,
          },
          y: {
            stacked: true,
            beginAtZero: true,
          },
        },
        plugins: {
          legend: {
            position: "bottom",
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generatePerformanceByCountryChart(
    exportData,
    openaiData,
    exchangeRatesData
  ) {
    const countryPerformance = {};

    const addPerformanceData = (items, type) => {
      items.forEach((item) => {
        const country = item.country || "Unknown";
        const duration = parseFloat(item.DurationMS || 0);

        if (country !== "Unknown" && duration > 0) {
          if (!countryPerformance[country]) {
            countryPerformance[country] = [];
          }
          countryPerformance[country].push(duration);
        }
      });
    };

    addPerformanceData(exportData, "export");
    addPerformanceData(openaiData, "openai");
    addPerformanceData(exchangeRatesData, "exchangeRates");

    if (Object.keys(countryPerformance).length === 0) {
      document.getElementById(
        "performanceByCountryChart"
      ).parentElement.innerHTML =
        '<div class="chart-no-data">No performance data by country available</div>';
      return;
    }

    const countryAverages = Object.entries(countryPerformance)
      .map(([country, durations]) => ({
        country,
        avgDuration:
          durations.reduce((sum, d) => sum + d, 0) / durations.length,
        count: durations.length,
      }))
      .filter((item) => item.count >= 5)
      .sort((a, b) => a.avgDuration - b.avgDuration)
      .slice(0, 10);

    new Chart(document.getElementById("performanceByCountryChart"), {
      type: "bar",
      data: {
        labels: countryAverages.map((item) => item.country),
        datasets: [
          {
            label: "Average Duration (ms)",
            data: countryAverages.map((item) => Math.round(item.avgDuration)),
            backgroundColor: "#10b981",
            borderColor: "#059669",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Average Duration (ms)",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateErrorRatesByCountryChart(
    errorData,
    exportData,
    openaiData,
    exchangeRatesData
  ) {
    if (errorData.length === 0) {
      document.getElementById(
        "errorRatesByCountryChart"
      ).parentElement.innerHTML =
        '<div class="chart-no-data"><h3>🎉 No Errors by Country!</h3><p>All regions running smoothly</p></div>';
      return;
    }

    const countryErrors = {};
    const countryOperations = {};

    errorData.forEach((error) => {
      const country = error.country || "Unknown";
      if (country !== "Unknown") {
        countryErrors[country] = (countryErrors[country] || 0) + 1;
      }
    });

    [...exportData, ...openaiData, ...exchangeRatesData].forEach((item) => {
      const country = item.country || "Unknown";
      if (country !== "Unknown") {
        countryOperations[country] = (countryOperations[country] || 0) + 1;
      }
    });

    const countryErrorRates = Object.keys(countryErrors)
      .map((country) => ({
        country,
        errorRate:
          ((countryErrors[country] || 0) / (countryOperations[country] || 1)) *
          100,
        errors: countryErrors[country] || 0,
        operations: countryOperations[country] || 0,
      }))
      .filter((item) => item.operations >= 5)
      .sort((a, b) => b.errorRate - a.errorRate);

    new Chart(document.getElementById("errorRatesByCountryChart"), {
      type: "bar",
      data: {
        labels: countryErrorRates.map((item) => item.country),
        datasets: [
          {
            label: "Error Rate (%)",
            data: countryErrorRates.map((item) => item.errorRate.toFixed(2)),
            backgroundColor: "#ef4444",
            borderColor: "#dc2626",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const item = countryErrorRates[context.dataIndex];
                return `${item.country}: ${item.errorRate.toFixed(2)}% (${
                  item.errors
                }/${item.operations})`;
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Error Rate (%)",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateSessionDurationByRegionChart(sessionData) {
    const sessionEndData = sessionData.filter(
      (s) => s.action === "SessionEnd" && s.duration > 0
    );

    if (sessionEndData.length === 0) {
      document.getElementById(
        "sessionDurationByRegionChart"
      ).parentElement.innerHTML =
        '<div class="chart-no-data">No session duration data by region available</div>';
      return;
    }

    const regionDurations = {};
    sessionEndData.forEach((session) => {
      const country = session.country || "Unknown";
      if (country !== "Unknown") {
        if (!regionDurations[country]) {
          regionDurations[country] = [];
        }
        regionDurations[country].push(parseFloat(session.duration));
      }
    });

    const regionAverages = Object.entries(regionDurations)
      .map(([country, durations]) => ({
        country,
        avgDuration:
          durations.reduce((sum, d) => sum + d, 0) / durations.length,
        count: durations.length,
      }))
      .filter((item) => item.count >= 2)
      .sort((a, b) => b.avgDuration - a.avgDuration);

    new Chart(document.getElementById("sessionDurationByRegionChart"), {
      type: "bar",
      data: {
        labels: regionAverages.map((item) => item.country),
        datasets: [
          {
            label: "Average Session Duration (seconds)",
            data: regionAverages.map((item) => Math.round(item.avgDuration)),
            backgroundColor: "#06b6d4",
            borderColor: "#0891b2",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Average Duration (seconds)",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }
  function generateTimezoneChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  ) {
    const allData = [
      ...exportData,
      ...openaiData,
      ...exchangeRatesData,
      ...googleSheetsData,
      ...sessionData,
      ...errorData,
    ];
    const timezoneCounts = {};

    allData.forEach((item) => {
      const timezone = item.timezone || "Unknown";
      if (timezone !== "Unknown") {
        timezoneCounts[timezone] = (timezoneCounts[timezone] || 0) + 1;
      }
    });

    if (Object.keys(timezoneCounts).length === 0) {
      document.getElementById("timezoneChart").parentElement.innerHTML =
        '<div class="chart-no-data">No timezone data available</div>';
      return;
    }

    const sortedTimezones = Object.entries(timezoneCounts)
      .sort(([, a], [, b]) => b - a)
      .slice(0, 10);

    new Chart(document.getElementById("timezoneChart"), {
      type: "bar",
      data: {
        labels: sortedTimezones.map(([tz]) => tz.replace("/", "/\n")),
        datasets: [
          {
            label: "Operations",
            data: sortedTimezones.map(([, count]) => count),
            backgroundColor: "#6366f1",
            borderColor: "#4f46e5",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Number of Operations",
            },
          },
          x: {
            ticks: {
              maxRotation: 45,
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  // Version Charts
  function generateVersionDistributionChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  ) {
    const allData = [
      ...exportData,
      ...openaiData,
      ...exchangeRatesData,
      ...googleSheetsData,
      ...sessionData,
      ...errorData,
    ];
    const versionCounts = {};

    allData.forEach((item) => {
      const version = item.appVersion || "Unknown";
      if (version !== "Unknown") {
        versionCounts[version] = (versionCounts[version] || 0) + 1;
      }
    });

    if (Object.keys(versionCounts).length === 0) {
      document.getElementById(
        "versionDistributionChart"
      ).parentElement.innerHTML =
        '<div class="chart-no-data">No version data available</div>';
      return;
    }

    const sortedVersions = Object.entries(versionCounts).sort(
      ([, a], [, b]) => b - a
    );

    const labels = sortedVersions.map(([version]) => `v${version}`);
    const data = sortedVersions.map(([, count]) => count);
    const colors = [
      "#3b82f6",
      "#10b981",
      "#f59e0b",
      "#ef4444",
      "#8b5cf6",
      "#06b6d4",
      "#84cc16",
      "#f97316",
      "#ec4899",
      "#6366f1",
    ];

    new Chart(document.getElementById("versionDistributionChart"), {
      type: "doughnut",
      data: {
        labels: labels,
        datasets: [
          {
            data: data,
            backgroundColor: colors.slice(0, labels.length),
            borderColor: colors
              .slice(0, labels.length)
              .map((c) => c.replace("0.8", "1")),
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((context.raw / total) * 100);
                return `${context.label}: ${context.raw} operations (${percentage}%)`;
              },
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateVersionTimeChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  ) {
    const allData = [
      ...exportData,
      ...openaiData,
      ...exchangeRatesData,
      ...googleSheetsData,
      ...sessionData,
      ...errorData,
    ];

    const versionByDate = {};
    const versions = new Set();

    allData.forEach((item) => {
      const date = new Date(item.timestamp).toLocaleDateString();
      const version = item.appVersion || "Unknown";

      if (version !== "Unknown") {
        versions.add(version);
        if (!versionByDate[date]) {
          versionByDate[date] = {};
        }
        versionByDate[date][version] = (versionByDate[date][version] || 0) + 1;
      }
    });

    if (versions.size === 0) {
      document.getElementById("versionTimeChart").parentElement.innerHTML =
        '<div class="chart-no-data">No version timeline data available</div>';
      return;
    }

    const sortedDates = Object.keys(versionByDate).sort();
    const recentDates = sortedDates.slice(-30);
    const topVersions = Array.from(versions).slice(0, 5);

    const datasets = topVersions.map((version, index) => ({
      label: `v${version}`,
      data: recentDates.map((date) => versionByDate[date]?.[version] || 0),
      borderColor: ["#3b82f6", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6"][
        index
      ],
      backgroundColor: [
        "rgba(59, 130, 246, 0.1)",
        "rgba(16, 185, 129, 0.1)",
        "rgba(245, 158, 11, 0.1)",
        "rgba(239, 68, 68, 0.1)",
        "rgba(139, 92, 246, 0.1)",
      ][index],
      fill: true,
      tension: 0.4,
    }));

    new Chart(document.getElementById("versionTimeChart"), {
      type: "line",
      data: {
        labels: recentDates,
        datasets: datasets,
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Operations Count",
            },
          },
          x: {
            ticks: {
              maxRotation: 45,
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateTopVersionsChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  ) {
    const allData = [
      ...exportData,
      ...openaiData,
      ...exchangeRatesData,
      ...googleSheetsData,
      ...sessionData,
      ...errorData,
    ];
    const versionStats = {};

    allData.forEach((item) => {
      const version = item.appVersion || "Unknown";
      if (version !== "Unknown") {
        if (!versionStats[version]) {
          versionStats[version] = {
            totalOps: 0,
            exports: 0,
            openai: 0,
            exchangeRates: 0,
            sessions: 0,
            errors: 0,
          };
        }
        versionStats[version].totalOps++;

        const dataType = item.dataType;
        switch (dataType) {
          case "Export":
            versionStats[version].exports++;
            break;
          case "OpenAI":
            versionStats[version].openai++;
            break;
          case "OpenExchangeRates":
            versionStats[version].exchangeRates++;
            break;
          case "Session":
            versionStats[version].sessions++;
            break;
          case "Error":
            versionStats[version].errors++;
            break;
        }
      }
    });

    if (Object.keys(versionStats).length === 0) {
      document.getElementById("topVersionsChart").parentElement.innerHTML =
        '<div class="chart-no-data">No version activity data available</div>';
      return;
    }

    const sortedVersions = Object.entries(versionStats)
      .sort(([, a], [, b]) => b.totalOps - a.totalOps)
      .slice(0, 8);

    const labels = sortedVersions.map(([version]) => `v${version}`);
    const chartExportData = sortedVersions.map(([, stats]) => stats.exports);
    const chartOpenaiData = sortedVersions.map(([, stats]) => stats.openai);
    const chartExchangeData = sortedVersions.map(
      ([, stats]) => stats.exchangeRates
    );
    const chartSessionData = sortedVersions.map(([, stats]) => stats.sessions);

    new Chart(document.getElementById("topVersionsChart"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Exports",
            data: chartExportData,
            backgroundColor: "#3b82f6",
          },
          {
            label: "OpenAI",
            data: chartOpenaiData,
            backgroundColor: "#8b5cf6",
          },
          {
            label: "Exchange Rates",
            data: chartExchangeData,
            backgroundColor: "#f59e0b",
          },
          {
            label: "Sessions",
            data: chartSessionData,
            backgroundColor: "#10b981",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            stacked: true,
          },
          y: {
            stacked: true,
            beginAtZero: true,
            title: {
              display: true,
              text: "Operations Count",
            },
          },
        },
        plugins: {
          legend: {
            position: "bottom",
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateVersionPerformanceChart(
    exportData,
    openaiData,
    exchangeRatesData
  ) {
    const performanceData = {};

    [...exportData, ...openaiData, ...exchangeRatesData].forEach((item) => {
      const version = item.appVersion || "Unknown";
      const duration = parseFloat(item.DurationMS || 0);

      if (version !== "Unknown" && duration > 0) {
        if (!performanceData[version]) {
          performanceData[version] = [];
        }
        performanceData[version].push(duration);
      }
    });

    if (Object.keys(performanceData).length === 0) {
      document.getElementById(
        "versionPerformanceChart"
      ).parentElement.innerHTML =
        '<div class="chart-no-data">No version performance data available</div>';
      return;
    }

    const versionAverages = Object.entries(performanceData)
      .map(([version, durations]) => ({
        version,
        avgDuration:
          durations.reduce((sum, d) => sum + d, 0) / durations.length,
        count: durations.length,
      }))
      .filter((item) => item.count >= 10)
      .sort((a, b) => a.avgDuration - b.avgDuration)
      .slice(0, 8);

    const labels = versionAverages.map((item) => `v${item.version}`);
    const averages = versionAverages.map((item) =>
      Math.round(item.avgDuration)
    );
    const colors = versionAverages.map(
      (_, index) =>
        [
          "#10b981",
          "#3b82f6",
          "#f59e0b",
          "#ef4444",
          "#8b5cf6",
          "#06b6d4",
          "#84cc16",
          "#f97316",
        ][index]
    );

    new Chart(document.getElementById("versionPerformanceChart"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Avg Duration (ms)",
            data: averages,
            backgroundColor: colors,
            borderColor: colors,
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const versionData = versionAverages[context.dataIndex];
                return `${context.label}: ${context.raw}ms avg (${versionData.count} operations)`;
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Average Duration (ms)",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateVersionSessionChart(sessionData) {
    const sessionEndData = sessionData.filter(
      (s) => s.action === "SessionEnd" && s.duration > 0
    );

    if (sessionEndData.length === 0) {
      document.getElementById("versionSessionChart").parentElement.innerHTML =
        '<div class="chart-no-data">No session duration by version data available</div>';
      return;
    }

    const versionSessions = {};
    sessionEndData.forEach((session) => {
      const version = session.appVersion || "Unknown";
      if (version !== "Unknown") {
        if (!versionSessions[version]) {
          versionSessions[version] = [];
        }
        versionSessions[version].push(parseFloat(session.duration));
      }
    });

    const versionAverages = Object.entries(versionSessions)
      .map(([version, durations]) => ({
        version,
        avgDuration:
          durations.reduce((sum, d) => sum + d, 0) / durations.length,
        count: durations.length,
      }))
      .filter((item) => item.count >= 3)
      .sort((a, b) => b.avgDuration - a.avgDuration);

    const labels = versionAverages.map((item) => `v${item.version}`);
    const averages = versionAverages.map((item) =>
      Math.round(item.avgDuration)
    );

    new Chart(document.getElementById("versionSessionChart"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Avg Session Duration (seconds)",
            data: averages,
            backgroundColor: "#06b6d4",
            borderColor: "#0891b2",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const versionData = versionAverages[context.dataIndex];
                return `${context.label}: ${context.raw}s avg (${versionData.count} sessions)`;
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Average Duration (seconds)",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateVersionErrorChart(
    errorData,
    exportData,
    openaiData,
    exchangeRatesData,
    sessionData
  ) {
    if (errorData.length === 0) {
      document.getElementById("versionErrorChart").parentElement.innerHTML =
        '<div class="chart-no-data"><h3>🎉 No Errors by Version!</h3><p>All versions running smoothly</p></div>';
      return;
    }

    const versionErrors = {};
    const versionOperations = {};

    errorData.forEach((error) => {
      const version = error.appVersion || "Unknown";
      if (version !== "Unknown") {
        versionErrors[version] = (versionErrors[version] || 0) + 1;
      }
    });

    [
      ...exportData,
      ...openaiData,
      ...exchangeRatesData,
      ...sessionData,
    ].forEach((item) => {
      const version = item.appVersion || "Unknown";
      if (version !== "Unknown") {
        versionOperations[version] = (versionOperations[version] || 0) + 1;
      }
    });

    const versionErrorRates = Object.keys(versionErrors)
      .map((version) => ({
        version,
        errorRate:
          ((versionErrors[version] || 0) / (versionOperations[version] || 1)) *
          100,
        errors: versionErrors[version] || 0,
        operations: versionOperations[version] || 0,
      }))
      .filter((item) => item.operations >= 10)
      .sort((a, b) => b.errorRate - a.errorRate);

    const labels = versionErrorRates.map((item) => `v${item.version}`);
    const errorRates = versionErrorRates.map((item) =>
      item.errorRate.toFixed(2)
    );

    new Chart(document.getElementById("versionErrorChart"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Error Rate (%)",
            data: errorRates,
            backgroundColor: "#ef4444",
            borderColor: "#dc2626",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const item = versionErrorRates[context.dataIndex];
                return `v${item.version}: ${item.errorRate.toFixed(2)}% (${
                  item.errors
                }/${item.operations})`;
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Error Rate (%)",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  // Error Charts
  function generateErrorCategoryChart(errorData) {
    if (errorData.length === 0) {
      document.getElementById("errorCategoryChart").parentElement.innerHTML =
        '<div class="chart-no-data"><h3>🎉 No Errors Detected!</h3><p>Your application is running smoothly</p></div>';
      return;
    }

    const categoryCounts = {};
    errorData.forEach((error) => {
      const category = error.ErrorCategory || "Unknown";
      categoryCounts[category] = (categoryCounts[category] || 0) + 1;
    });

    const sortedCategories = Object.entries(categoryCounts).sort(
      ([, a], [, b]) => b - a
    );
    const labels = sortedCategories.map(([category]) => category);
    const data = sortedCategories.map(([, count]) => count);
    const colors = [
      "#ef4444",
      "#f97316",
      "#eab308",
      "#84cc16",
      "#22c55e",
      "#06b6d4",
      "#6366f1",
      "#8b5cf6",
    ];

    new Chart(document.getElementById("errorCategoryChart"), {
      type: "pie",
      data: {
        labels: labels,
        datasets: [
          {
            data: data,
            backgroundColor: colors.slice(0, labels.length),
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((context.raw / total) * 100);
                return `${context.label}: ${context.raw} errors (${percentage}%)`;
              },
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateErrorCodeChart(errorData) {
    if (errorData.length === 0) {
      document.getElementById("errorCodeChart").parentElement.innerHTML =
        '<div class="chart-no-data"><h3>🎉 No Error Codes!</h3><p>Clean codebase detected</p></div>';
      return;
    }

    const codeCounts = {};
    errorData.forEach((error) => {
      const code = error.ErrorCode || "Unknown";
      codeCounts[code] = (codeCounts[code] || 0) + 1;
    });

    const sortedCodes = Object.entries(codeCounts)
      .sort(([, a], [, b]) => b - a)
      .slice(0, 10);

    new Chart(document.getElementById("errorCodeChart"), {
      type: "bar",
      data: {
        labels: sortedCodes.map(([code]) => code),
        datasets: [
          {
            label: "Error Occurrences",
            data: sortedCodes.map(([, count]) => count),
            backgroundColor: "#ef4444",
            borderColor: "#dc2626",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: "y",
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          x: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Number of Occurrences",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateErrorTimeChart(errorData) {
    if (errorData.length === 0) {
      document.getElementById("errorTimeChart").parentElement.innerHTML =
        '<div class="chart-no-data"><h3>📈 Stable Performance</h3><p>No error trends to display</p></div>';
      return;
    }

    const dailyErrors = {};
    errorData.forEach((error) => {
      const date = error.timestamp.split(" ")[0];
      dailyErrors[date] = (dailyErrors[date] || 0) + 1;
    });

    const dates = Object.keys(dailyErrors).sort();
    const errorCounts = dates.map((date) => dailyErrors[date]);

    new Chart(document.getElementById("errorTimeChart"), {
      type: "line",
      data: {
        labels: dates,
        datasets: [
          {
            label: "Daily Error Count",
            data: errorCounts,
            backgroundColor: "rgba(239, 68, 68, 0.1)",
            borderColor: "#ef4444",
            borderWidth: 2,
            fill: true,
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Error Count",
            },
          },
          x: {
            ticks: {
              maxRotation: 45,
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateStabilityChart(
    exportData,
    openaiData,
    exchangeRatesData,
    sessionData,
    errorData
  ) {
    const totalOperations =
      exportData.length +
      openaiData.length +
      exchangeRatesData.length +
      sessionData.length;
    const totalErrors = errorData.length;
    const successfulOperations = totalOperations - totalErrors;

    if (totalOperations === 0) {
      document.getElementById("stabilityChart").parentElement.innerHTML =
        '<div class="chart-no-data">No operations data available</div>';
      return;
    }

    new Chart(document.getElementById("stabilityChart"), {
      type: "doughnut",
      data: {
        labels: ["Successful Operations", "Errors"],
        datasets: [
          {
            data: [successfulOperations, totalErrors],
            backgroundColor: ["#10b981", "#ef4444"],
            borderColor: ["#059669", "#dc2626"],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((context.raw / total) * 100);
                return `${
                  context.label
                }: ${context.raw.toLocaleString()} (${percentage}%)`;
              },
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  // Usage Charts
  function generateSessionDurationChart(sessionData) {
    const sessionEndData = sessionData.filter(
      (s) => s.action === "SessionEnd" && s.duration > 0
    );

    if (sessionEndData.length === 0) {
      document.getElementById("sessionDurationChart").parentElement.innerHTML =
        '<div class="chart-no-data">No session duration data available</div>';
      return;
    }

    const dailyStats = {};
    sessionEndData.forEach((session) => {
      const date = session.timestamp.split(" ")[0];
      if (!dailyStats[date]) {
        dailyStats[date] = {
          totalDuration: 0,
          count: 0,
        };
      }
      dailyStats[date].totalDuration += parseFloat(session.duration);
      dailyStats[date].count++;
    });

    const dates = Object.keys(dailyStats).sort();
    const avgDurations = dates.map((date) =>
      Math.round(dailyStats[date].totalDuration / dailyStats[date].count)
    );

    new Chart(document.getElementById("sessionDurationChart"), {
      type: "bar",
      data: {
        labels: dates,
        datasets: [
          {
            label: "Average Session Duration (seconds)",
            data: avgDurations,
            backgroundColor: "#3b82f6",
            borderColor: "#2563eb",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Duration (seconds)",
            },
          },
          x: {
            ticks: {
              maxRotation: 45,
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateExportTypesBreakdown(exportData) {
    if (exportData.length === 0) {
      document.getElementById("exportTypesGrid").parentElement.innerHTML =
        '<div class="chart-no-data">No export data available</div>';
      return;
    }

    const typeCounts = {};
    exportData.forEach((item) => {
      const type = item.ExportType || "Unknown";
      typeCounts[type] = (typeCounts[type] || 0) + 1;
    });

    const sortedTypes = Object.entries(typeCounts).sort(
      ([, a], [, b]) => b - a
    );
    const labels = sortedTypes.map(([type]) => type);
    const data = sortedTypes.map(([, count]) => count);
    const colors = labels.map((type) => typeColors[type] || "#9ca3af");

    new Chart(document.getElementById("exportTypesGrid"), {
      type: "pie",
      data: {
        labels: labels,
        datasets: [
          {
            data: data,
            backgroundColor: colors,
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((context.raw / total) * 100);
                return `${context.label}: ${context.raw} exports (${percentage}%)`;
              },
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateExportDurationByTypeChart(exportData) {
    if (exportData.length === 0) {
      document.getElementById(
        "exportDurationByTypeChart"
      ).parentElement.innerHTML =
        '<div class="chart-no-data">No export data available</div>';
      return;
    }

    const typeAverages = {};
    const typeCounts = {};

    exportData.forEach((item) => {
      const type = item.ExportType || "Unknown";
      const duration = item.DurationMS;
      const durationValue =
        typeof duration === "string"
          ? parseFloat(duration.replace(/[^\d.]/g, ""))
          : parseInt(duration) || 0;

      if (!typeAverages[type]) {
        typeAverages[type] = 0;
        typeCounts[type] = 0;
      }
      typeAverages[type] += durationValue;
      typeCounts[type]++;
    });

    const labels = [];
    const averages = [];
    const colors = [];

    for (const type in typeAverages) {
      if (typeCounts[type] > 0) {
        labels.push(type);
        averages.push(Math.round(typeAverages[type] / typeCounts[type]));
        colors.push(typeColors[type] || "#9ca3af");
      }
    }

    new Chart(document.getElementById("exportDurationByTypeChart"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Average Duration (ms)",
            data: averages,
            backgroundColor: colors,
            borderColor: colors.map((c) => c.replace("0.6", "1")),
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Duration (ms)",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateExportFileSizeByTypeChart(exportData) {
    const filteredData = exportData
      .filter((d) => d.FileSize && d.FileSize !== "null" && d.FileSize !== null)
      .map((d) => ({
        type: d.ExportType || "Unknown",
        size: parseInt(d.FileSize) || 0,
      }))
      .filter((item) => item.size > 0);

    if (filteredData.length === 0) {
      document.getElementById(
        "exportFileSizeByTypeChart"
      ).parentElement.innerHTML =
        '<div class="chart-no-data">No file size data available</div>';
      return;
    }

    const typeAverages = {};
    const typeCounts = {};

    filteredData.forEach((item) => {
      if (!typeAverages[item.type]) {
        typeAverages[item.type] = 0;
        typeCounts[item.type] = 0;
      }
      typeAverages[item.type] += item.size;
      typeCounts[item.type]++;
    });

    const labels = [];
    const averages = [];
    const colors = [];

    for (const type in typeAverages) {
      if (typeCounts[type] > 0) {
        labels.push(type);
        averages.push(Math.round(typeAverages[type] / typeCounts[type]));
        colors.push(typeColors[type] || "#9ca3af");
      }
    }

    new Chart(document.getElementById("exportFileSizeByTypeChart"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Average File Size (bytes)",
            data: averages,
            backgroundColor: colors,
            borderColor: colors.map((c) => c.replace("0.6", "1")),
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "File Size (bytes)",
            },
            ticks: {
              callback: function (value) {
                if (value >= 1048576)
                  return (value / 1048576).toFixed(1) + "MB";
                if (value >= 1024) return (value / 1024).toFixed(1) + "KB";
                return value + "B";
              },
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  // API Usage Charts
  function generateExportDurationChart(exportData) {
    if (exportData.length === 0) {
      document.getElementById("exportDurationChart").parentElement.innerHTML =
        '<div class="chart-no-data">No export data available</div>';
      return;
    }

    const recentData = exportData.slice(-100);
    const labels = recentData.map((d, index) => {
      const date = new Date(d.timestamp);
      return recentData.length > 50
        ? index % Math.ceil(recentData.length / 20) === 0
          ? date.toLocaleDateString()
          : ""
        : date.toLocaleString();
    });

    const durations = recentData.map((d) => {
      const duration = d.DurationMS;
      return typeof duration === "string"
        ? parseFloat(duration.replace(/[^\d.]/g, ""))
        : parseInt(duration) || 0;
    });

    new Chart(document.getElementById("exportDurationChart"), {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Duration (ms)",
            data: durations,
            borderColor: "#3b82f6",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            fill: true,
            tension: 0.4,
            pointRadius: exportData.length > 50 ? 1 : 3,
            pointHoverRadius: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          x: {
            display: true,
            ticks: {
              maxTicksLimit: 15,
              maxRotation: 45,
            },
          },
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Duration (milliseconds)",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateOpenAIChart(openaiData) {
    if (openaiData.length === 0) {
      document.getElementById("openaiChart").parentElement.innerHTML =
        '<div class="chart-no-data">No OpenAI data available</div>';
      return;
    }

    const modelCounts = {};
    openaiData.forEach((item) => {
      const model = item.Model || "Unknown";
      modelCounts[model] = (modelCounts[model] || 0) + 1;
    });

    new Chart(document.getElementById("openaiChart"), {
      type: "pie",
      data: {
        labels: Object.keys(modelCounts),
        datasets: [
          {
            data: Object.values(modelCounts),
            backgroundColor: [
              "#3b82f6",
              "#10b981",
              "#f59e0b",
              "#ef4444",
              "#8b5cf6",
              "#06b6d4",
            ],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateOpenAITokenChart(openaiData) {
    if (openaiData.length === 0) {
      document.getElementById("openaiTokenChart").parentElement.innerHTML =
        '<div class="chart-no-data">No OpenAI token data available</div>';
      return;
    }

    const recentData = openaiData.slice(-50);
    const labels = recentData.map((d, index) => `Call ${index + 1}`);
    const tokens = recentData.map((d) => parseInt(d.TokensUsed) || 0);

    new Chart(document.getElementById("openaiTokenChart"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Tokens Used",
            data: tokens,
            backgroundColor: "#8b5cf6",
            borderColor: "#7c3aed",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Tokens",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateExchangeRatesChart(exchangeRatesData) {
    if (exchangeRatesData.length === 0) {
      document.getElementById("exchangeRatesChart").parentElement.innerHTML =
        '<div class="chart-no-data">No exchange rates data available</div>';
      return;
    }

    const recentData = exchangeRatesData.slice(-30);
    const labels = recentData.map((d) =>
      new Date(d.timestamp).toLocaleDateString()
    );
    const durations = recentData.map((d) => parseInt(d.DurationMS) || 0);

    new Chart(document.getElementById("exchangeRatesChart"), {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Response Time (ms)",
            data: durations,
            borderColor: "#f59e0b",
            backgroundColor: "rgba(245, 158, 11, 0.1)",
            fill: true,
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Duration (ms)",
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  // Overall Activity Charts
  function generateExportFileSizeChart(exportData) {
    const fileSizes = exportData
      .filter((d) => d.FileSize && d.FileSize !== "null" && d.FileSize !== null)
      .map((d) => ({
        size: parseInt(d.FileSize) || 0,
        type: d.ExportType || "Unknown",
      }))
      .filter((item) => item.size > 0);

    if (fileSizes.length === 0) {
      document.getElementById("exportFileSizeChart").parentElement.innerHTML =
        '<div class="chart-no-data">No file size data available</div>';
      return;
    }

    const datasets = [];
    const types = [...new Set(fileSizes.map((item) => item.type))];

    types.forEach((type) => {
      const typeData = fileSizes.filter((item) => item.type === type);
      datasets.push({
        label: type,
        data: typeData.map((item, index) => ({
          x: index + 1,
          y: item.size,
        })),
        backgroundColor: typeColors[type] || "#9ca3af",
        borderColor: typeColors[type] || "#9ca3af",
        pointRadius: 4,
      });
    });

    new Chart(document.getElementById("exportFileSizeChart"), {
      type: "scatter",
      data: {
        datasets: datasets,
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
        },
        scales: {
          x: {
            title: {
              display: true,
              text: "Export Number",
            },
          },
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "File Size (bytes)",
            },
            ticks: {
              callback: function (value) {
                if (value >= 1048576)
                  return (value / 1048576).toFixed(1) + "MB";
                if (value >= 1024) return (value / 1024).toFixed(1) + "KB";
                return value + "B";
              },
            },
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }

  function generateOverallActivityChart(
    exportData,
    openaiData,
    exchangeRatesData,
    googleSheetsData,
    sessionData,
    errorData
  ) {
    const allData = [
      ...exportData.map((d) => ({
        ...d,
        type: "Export",
      })),
      ...openaiData.map((d) => ({
        ...d,
        type: "OpenAI",
      })),
      ...exchangeRatesData.map((d) => ({
        ...d,
        type: "Exchange Rates",
      })),
      ...googleSheetsData.map((d) => ({
        ...d,
        type: "Google Sheets",
      })),
      ...sessionData.map((d) => ({
        ...d,
        type: "Session",
      })),
      ...errorData.map((d) => ({
        ...d,
        type: "Error",
      })),
    ];

    if (allData.length === 0) {
      document.getElementById("overallActivityChart").parentElement.innerHTML =
        '<div class="chart-no-data">No activity data available</div>';
      return;
    }

    const dailyCounts = {};
    allData.forEach((item) => {
      const date = new Date(item.timestamp).toLocaleDateString();
      if (!dailyCounts[date]) {
        dailyCounts[date] = {
          Export: 0,
          OpenAI: 0,
          "Exchange Rates": 0,
          "Google Sheets": 0,
          Session: 0,
          Error: 0,
        };
      }
      dailyCounts[date][item.type]++;
    });

    const sortedDates = Object.keys(dailyCounts).sort();
    const recent30Dates = sortedDates.slice(-30);

    const datasets = [
      {
        label: "Exports",
        data: recent30Dates.map((date) => dailyCounts[date].Export),
        backgroundColor: "#3b82f6",
      },
      {
        label: "OpenAI",
        data: recent30Dates.map((date) => dailyCounts[date].OpenAI),
        backgroundColor: "#8b5cf6",
      },
      {
        label: "Exchange Rates",
        data: recent30Dates.map((date) => dailyCounts[date]["Exchange Rates"]),
        backgroundColor: "#f59e0b",
      },
      {
        label: "Google Sheets",
        data: recent30Dates.map((date) => dailyCounts[date]["Google Sheets"]),
        backgroundColor: "#10b981",
      },
      {
        label: "Sessions",
        data: recent30Dates.map((date) => dailyCounts[date].Session),
        backgroundColor: "#06b6d4",
      },
      {
        label: "Errors",
        data: recent30Dates.map((date) => dailyCounts[date].Error),
        backgroundColor: "#ef4444",
      },
    ];

    const activeDatasets = datasets.filter((dataset) =>
      dataset.data.some((value) => value > 0)
    );

    new Chart(document.getElementById("overallActivityChart"), {
      type: "bar",
      data: {
        labels: recent30Dates,
        datasets: activeDatasets,
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            stacked: true,
          },
          y: {
            stacked: true,
            beginAtZero: true,
            title: {
              display: true,
              text: "Operations Count",
            },
          },
        },
        plugins: {
          legend: {
            position: "bottom",
          },
        },
        layout: {
          padding: {
            bottom: 40,
          },
        },
      },
    });
  }
});
