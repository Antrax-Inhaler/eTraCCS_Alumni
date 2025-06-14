/**
 * Export a single chart to an image file
 * @param {Chart} chart - Chart.js instance
 * @param {string} filename - Name for the exported file (without extension)
 */
export const exportChartToImage = (chart, filename = 'chart') => {
  if (!chart) return;
  
  // Create a temporary link element
  const link = document.createElement('a');
  link.download = `${filename}.png`;
  
  // Get the chart as base64 image
  const image = chart.toBase64Image('image/png', 1);
  link.href = image;
  
  // Trigger download
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

/**
 * Export multiple charts as a zip file
 * @param {Chart[]} charts - Array of Chart.js instances
 * @param {string} zipName - Name for the zip file (without extension)
 */
export const exportMultipleCharts = async (charts, zipName = 'charts') => {
  // Dynamically import JSZip only when needed
  const JSZip = await import('jszip');
  const zip = new JSZip.default();
  
  // Add each chart to the zip
  charts.forEach((chart, index) => {
    if (!chart) return;
    const image = chart.toBase64Image('image/png', 1).split(',')[1];
    zip.file(`chart-${index + 1}.png`, image, { base64: true });
  });
  
  // Generate and download the zip
  const content = await zip.generateAsync({ type: 'blob' });
  const url = URL.createObjectURL(content);
  
  const link = document.createElement('a');
  link.href = url;
  link.download = `${zipName}.zip`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  
  // Clean up
  setTimeout(() => URL.revokeObjectURL(url), 100);
};