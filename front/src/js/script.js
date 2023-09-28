document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('elementCounterForm');
    const responseArea = document.getElementById('responseArea');

    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        const url = document.getElementById('url').value;
        const element = document.getElementById('element').value;

        try {
            const response = await fetch('count_elements.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `url=${encodeURIComponent(url)}&element=${encodeURIComponent(element)}`,
            });

            if (response.ok) {
                const data = await response.json();
                responseArea.textContent = data.message;
            } else {
                console.error('Error:', response.statusText);
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    });
});

