document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const messageInput = document.querySelector('.input-message');
    const chatBox = document.querySelector('.message-container');
    

    // ส่งข้อความเมื่อกดปุ่มส่ง
    form.addEventListener('submit', (e) => {
        

        const message = messageInput.value;

        if (message.trim() !== '') {
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `message=${encodeURIComponent(message)}`
            })
            .then(response => response.text())
            .then(() => {
                messageInput.value = ''; // ล้างข้อความ
                loadMessages(); // โหลดข้อความใหม่
            })
            .catch(error => console.error('Error:', error));
        }
    });

    
    function loadMessages() {
        fetch('load_messages.php')
            .then(response => response.text())
            .then(data => {
                chatBox.innerHTML = data;
                chatBox.scrollTop = chatBox.scrollHeight; // เลื่อนลงล่างสุด
            })
            .catch(error => console.error('Error:', error));
    }

    // โหลดข้อความทุก 1 วินาที
    setInterval(loadMessages, 1000);
});


// Function to handle scrolling and showing/hiding the button
function handleScroll() {
    var chatBox = document.querySelector('.chat-box');
    var scrollButton = document.querySelector('.scroll-to-bottom');
    var threshold = 50; 

    // Check if the user is close enough to the bottom (within the threshold)
    if (chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight <= threshold) {
        // User is close to the bottom, hide the button
        scrollButton.style.display = 'none';
    } else {
        // User is not close to the bottom, show the button
        scrollButton.style.display = 'block';
    }
}

// Add the scroll event listener to the chat box
document.querySelector('.chat-box').addEventListener('scroll', handleScroll);

// Add event listener for the button to scroll to the bottom
document.querySelector('.scroll-to-bottom').addEventListener('click', function() {
    var chatBox = document.querySelector('.chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;
    handleScroll(); // Call handleScroll to recheck the scroll position after clicking
});
// Initially call handleScroll when the page loads
window.onload = function() {
    handleScroll();
    var chatBox = document.querySelector('.chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;  // Hide or show the button based on initial scroll position
};


