document.addEventListener('DOMContentLoaded', () => {
    const calendar = document.getElementById('calendar');
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();

    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const firstDay = new Date(year, month, 1).getDay();

    let html = '<div class="calendar-grid">';
    // Add empty cells for offset
    for (let i = 0; i < firstDay; i++) html += '<div></div>';

    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        // In real app: fetch availability via AJAX
        const isBooked = Math.random() > 0.7; // Simulate
        html += `<div class="${isBooked ? 'booked' : 'available'}">${day}</div>`;
    }
    html += '</div>';
    calendar.innerHTML = html;
});