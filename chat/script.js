document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const messageInput = document.querySelector('.input-message');
    const chatBox = document.querySelector('.message-container');

    // ส่งข้อความเมื่อกดปุ่มส่ง
    form.addEventListener('submit', (e) => {
        e.preventDefault(); // ป้องกันการรีเฟรชหน้า

        const message = messageInput.value;
        const currentScrollPosition = chatBox.scrollTop; // บันทึกตำแหน่ง scroll ปัจจุบัน

        if (message.trim() !== '') {
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `message=${encodeURIComponent(message)}`
            })
            .then(response => response.text())
            .then(() => {
                messageInput.value = ''; // ล้างข้อความ
                loadMessages(currentScrollPosition); // โหลดข้อความใหม่โดยส่งตำแหน่ง scroll
            })
            .catch(error => console.error('Error:', error));
        }
    });

    // ฟังก์ชันโหลดข้อความ โดยรับพารามิเตอร์ตำแหน่ง scroll
    function loadMessages(scrollPosition = null) {
        fetch('load_messages.php')
            .then(response => response.text())
            .then(data => {
                chatBox.innerHTML = data;
                // ถ้ามีตำแหน่ง scroll ที่ส่งมา ให้คืนค่าตำแหน่งนั้น มิฉะนั้นเลื่อนไปล่างสุด
                if (scrollPosition !== null) {
                    chatBox.scrollTop = scrollPosition;
                } else {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // โหลดข้อความทุก 1 วินาที (รักษาตำแหน่ง scroll ปัจจุบัน)
    setInterval(() => {
        const currentScroll = chatBox.scrollTop;
        loadMessages(currentScroll);
    }, 1000);

    // โหลดข้อความครั้งแรกเมื่อหน้าโหลด
    loadMessages();
});

// Function to handle scrolling and showing/hiding the button
function handleScroll() {
    const chatBox = document.querySelector('.chat-box');
    const scrollButton = document.querySelector('.scroll-to-bottom');
    const threshold = 50;

    if (chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight <= threshold) {
        scrollButton.style.display = 'none';
    } else {
        scrollButton.style.display = 'block';
    }
}

// Add event listeners
const chatBox = document.querySelector('.chat-box');
chatBox.addEventListener('scroll', handleScroll);

document.querySelector('.scroll-to-bottom').addEventListener('click', () => {
    chatBox.scrollTop = chatBox.scrollHeight;
    handleScroll();
});

// เรียก handleScroll และเลื่อนไปล่างสุดเมื่อโหลดหน้า
window.onload = () => {
    handleScroll();
    chatBox.scrollTop = chatBox.scrollHeight;
};