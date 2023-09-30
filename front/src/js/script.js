const baseContainerClass = '.response-block-field__text';

document.addEventListener('DOMContentLoaded', () => {
  // Function to get an element by appending the base class modifier
  const getElement = (className) =>
    document.querySelector(`${baseContainerClass}--${className}`);

  // Select DOM elements
  const form = document.getElementById('elementCounterForm');
  const responseElements = {
    url: getElement('url'),
    date: getElement('date'),
    time: getElement('time'),
    message: getElement('message'),
    totalDomainURLs: getElement('total-domain-urls'),
    averageLoadTime: getElement('average-time'),
    domainElements: document.querySelectorAll('.response-block-field__domain'),
    elementTags: document.querySelectorAll('.response-block-field__element'),
    totalDomainElementCounts: getElement('total-domain-elements-count'),
    totalElementCounts: getElement('total-elements-count'),
  };

  // Array of loading elements, including the search button
  const totalLoadingElements = [
    ...document.querySelectorAll('.loading-element'),
    document.querySelector('.search-form__button'),
  ];

  // Function to display a loader inside an element
  function showLoader(element) {
    element.textContent = '';
    const loader = document.createElement('div');
    const loaderClasses =
      element.classList.contains('search-form__button') ||
      element.classList.contains(`${baseContainerClass}--message`)
        ? ['loader', 'loader--center']
        : ['loader'];
    loader.classList.add(...loaderClasses);
    element.appendChild(loader);
  }

  // Function to hide a loader inside an element
  function hideLoader(element) {
    element.querySelector('.loader').remove();
  }

  // Function to send a request and update data
  async function fetchData() {
    // Get values from input fields
    const urlValue = document.getElementById('url').value;
    const elementValue = document.getElementById('element').value;

    // Update element tags with the selected HTML element
    responseElements.elementTags.forEach(
      (el) => (el.textContent = `<${elementValue}>`)
    );

    // Show loaders in loading elements
    totalLoadingElements.forEach((el) => showLoader(el));
    const searchingButton = totalLoadingElements.find((el) =>
      el.classList.contains('search-form__button')
    );
    searchingButton.disabled = true;

    try {
      // Send a POST request to the server
      const response = await fetch('../../../backend/count_elements.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `url=${encodeURIComponent(urlValue)}&element=${encodeURIComponent(
          elementValue
        )}`,
      });

      if (response.ok) {
        const data = await response.json();
        if (data.is_error) {
          console.error(data.error_message); // Log error message
        } else {
          // Hide loaders and enable the "Search" button
          totalLoadingElements.forEach((el) => hideLoader(el));
          searchingButton.disabled = false;
          searchingButton.textContent = 'Search';

          // Update response elements with fetched data
          responseElements.url.textContent = data.url; // Put searching url
          responseElements.date.textContent = data.date; // Put searching date and time
          responseElements.time.textContent = data.time; // Put searching duration
          responseElements.message.textContent = data.message; // Put searching result message
          responseElements.totalDomainURLs.textContent = data.total_domain_urls; // Put count of urls with one domain
          responseElements.averageLoadTime.textContent = data.average_load_time; // Put average load duration from one domain during last 24h
          responseElements.totalDomainElementCounts.textContent =
            data.total_domain_element_counts; // Put total count of element with one domain during all time
          responseElements.totalElementCounts.textContent =
            data.total_element_counts; // Put total count of element during all time

          // Put searching domain
          responseElements.domainElements.forEach(
            (el) => (el.textContent = data.domain_name)
          );
        }
      } else {
        console.error('Error:', response.statusText);
      }
    } catch (error) {
      console.error('Fetch error:', error);
    }
  }

  // Add a submit event listener to the form to initiate data fetching
  form.addEventListener('submit', (event) => {
    event.preventDefault(); // Prevent form submission
    fetchData(); // Call the fetchData function
  });
});
