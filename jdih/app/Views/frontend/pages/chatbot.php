<?= $this->extend('layouts/frontend') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/chatbot.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-robot icon-sm me-1"></i> Asisten Virtual</span>
                </div>
                <h1 class="hero-title">Asisten JDIH</h1>
                <p class="hero-subtitle">Tanyakan apa saja tentang produk hukum Kota Padang</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Chat Area -->
            <div class="col-lg-8">
                <div class="chat-wrapper shadow-lg">
                    <div class="chat-area" id="chatArea">
                        <div class="message bot-message">
                            <p>Halo!<br>Saya adalah asisten virtual JDIH Kota Padang. Ada yang bisa saya bantu terkait produk hukum?</p>
                        </div>
                        <!-- Dynamic messages will be appended here -->
                    </div>
                    <div class="chat-input-area">
                        <textarea id="userInput" placeholder="Ketik pertanyaan Anda di sini..." aria-label="Ketik pertanyaan untuk Chatbot" spellcheck="false" required></textarea>
                        <button id="sendButton" class="btn btn-primary" aria-label="Kirim Pesan"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Panduan & Contoh</h5>
                    </div>
                    <div class="card-body p-4">
                        <h6 class="text-dark fw-bold">Panduan Pengguna</h6>
                        <p class="small text-muted mb-3">Anda bisa menanyakan tentang:</p>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex align-items-center border-0 px-0">
                                <i class="fas fa-gavel text-primary me-3 fa-fw"></i>
                                <span class="small">Peraturan Daerah (Perda) & Perwal</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center border-0 px-0">
                                <i class="fas fa-check-circle text-success me-3 fa-fw"></i>
                                <span class="small">Status sebuah peraturan</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center border-0 px-0">
                                <i class="fas fa-file-alt text-info me-3 fa-fw"></i>
                                <span class="small">Ringkasan atau abstrak</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center border-0 px-0">
                                <i class="fas fa-download text-warning me-3 fa-fw"></i>
                                <span class="small">Link unduhan dokumen</span>
                            </li>
                        </ul>

                        <h6 class="text-dark fw-bold">Contoh Pertanyaan</h6>
                        <p class="small text-muted mb-3">Klik salah satu contoh untuk memulai:</p>
                        <div class="d-grid gap-2 example-questions">
                            <button class="btn btn-outline-primary btn-sm example-question-btn text-start">
                                <i class="fas fa-search me-2"></i>Perda tentang Ketenteraman Dan Ketertiban Umum
                            </button>
                            <button class="btn btn-outline-primary btn-sm example-question-btn text-start">
                                <i class="fas fa-download me-2"></i>Unduh Perda Pajak Daerah Dan Retribusi Daerah
                            </button>
                            <button class="btn btn-outline-primary btn-sm example-question-btn text-start">
                                <i class="fas fa-file-alt me-2"></i>Ringkasan Perda tentang Pemberdayaan Usaha Mikro
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatArea = document.getElementById('chatArea');
        const userInput = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');
        const csrfTokenName = '<?php echo csrf_token() ?>';
        let csrfTokenHash = '<?php echo csrf_hash() ?>';

        // Fungsi untuk membersihkan HTML dari string untuk mencegah XSS
        function sanitizeHTML(str) {
            const temp = document.createElement('div');
            temp.textContent = str;
            return temp.innerHTML;
        }

        // Fungsi untuk mem-parsing dan memformat respons bot dengan aman
        function parseBotResponse(text) {
            // 1. Sanitasi input mentah untuk mengubah karakter HTML menjadi entitas
            // Ini adalah langkah keamanan utama untuk mencegah XSS.
            const sanitizedText = sanitizeHTML(text);

            // 2. Ubah **teks tebal** menjadi <strong>teks tebal</strong>
            let formattedText = sanitizedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            // 3. Hapus penanda daftar '*' dari awal setiap baris (flag 'gm' untuk global & multiline)
            formattedText = formattedText.replace(/^\s*\*\s/gm, '');

            // 4. Ubah link markdown [teks](url) menjadi tombol HTML dengan validasi URL
            const linkRegex = /\[([^\]]+)\]\(([^)]+)\)/g;
            formattedText = formattedText.replace(linkRegex, (match, linkText, url) => {
                // Validasi URL: hanya izinkan http, https, atau tautan relatif yang aman
                const sanitizedUrl = sanitizeHTML(url.trim());
                const isSafeUrl = sanitizedUrl.startsWith('http://') ||
                    sanitizedUrl.startsWith('https://') ||
                    (sanitizedUrl.startsWith('/') && !sanitizedUrl.startsWith('//'));

                if (isSafeUrl) {
                    return `<a href="${sanitizedUrl}" target="_blank" rel="noopener noreferrer" class="chat-download-btn">${sanitizeHTML(linkText)}</a>`;
                }
                // Jika URL tidak aman, kembalikan teks tautan sebagai teks biasa
                return sanitizeHTML(linkText);
            });

            // 5. Terakhir, ubah karakter baris baru (\n) menjadi tag <br>
            // Ini aman dilakukan setelah sanitasi.
            formattedText = formattedText.replace(/\n/g, '<br>');

        // Fungsi untuk memberikan efek mengetik pada respons bot
        function typeWriter(element, html, speed = 15) {
            let i = 0;
            element.innerHTML = "";
            
            // Temporary div to parse HTML nodes
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const nodes = Array.from(tempDiv.childNodes);
            
            function renderNode(nodeIndex) {
                if (nodeIndex >= nodes.length) {
                    chatArea.scrollTo(0, chatArea.scrollHeight);
                    return;
                }
                
                const node = nodes[nodeIndex];
                if (node.nodeType === Node.TEXT_NODE) {
                    let charIndex = 0;
                    const text = node.textContent;
                    function typeText() {
                        if (charIndex < text.length) {
                            element.innerHTML += text.charAt(charIndex);
                            charIndex++;
                            chatArea.scrollTo(0, chatArea.scrollHeight);
                            setTimeout(typeText, speed);
                        } else {
                            renderNode(nodeIndex + 1);
                        }
                    }
                    typeText();
                } else {
                    // It's an HTML element (like <br> or <strong>)
                    element.innerHTML += node.outerHTML;
                    chatArea.scrollTo(0, chatArea.scrollHeight);
                    renderNode(nodeIndex + 1);
                }
            }
            
            renderNode(0);
        }

        function appendMessage(sender, text, interactionId = null) {

            const messageWrapper = document.createElement('div');
            messageWrapper.className = `message ${sender}-message`;

            const messagePara = document.createElement('p');

            if (sender === 'bot') {
                // Proses respons bot untuk merender HTML dengan efek mengetik
                const htmlContent = parseBotResponse(text);
                typeWriter(messagePara, htmlContent);
            } else {
                // Tampilkan pesan pengguna sebagai teks biasa
                messagePara.textContent = text;
            }

            messageWrapper.appendChild(messagePara);

            if (sender === 'bot' && interactionId) {
                const feedbackContainer = document.createElement('div');
                feedbackContainer.className = 'feedback-container';
                feedbackContainer.dataset.interactionId = interactionId;
                feedbackContainer.innerHTML = `
                    <button class="feedback-btn like" title="Jawaban ini membantu"><i class="fas fa-thumbs-up"></i></button>
                    <button class="feedback-btn dislike" title="Jawaban ini tidak membantu"><i class="fas fa-thumbs-down"></i></button>
                `;
                messageWrapper.appendChild(feedbackContainer);
            }

            chatArea.appendChild(messageWrapper);
            chatArea.scrollTo(0, chatArea.scrollHeight);
        }

        // Fungsi untuk menampilkan indikator pengetikan
        function showTypingIndicator() {
            // Hapus indikator lama jika ada
            removeTypingIndicator();
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot-message';
            typingDiv.id = 'typingIndicator';
            typingDiv.innerHTML = `<p><span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span></p>`;
            chatArea.appendChild(typingDiv);
            chatArea.scrollTo(0, chatArea.scrollHeight);
        }

        // Fungsi untuk menghapus indikator pengetikan
        function removeTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        // Fungsi untuk mengirim pesan
        async function sendMessage() {
            const query = userInput.value.trim();
            if (query === '') return;

            appendMessage('user', query);
            userInput.value = '';
            userInput.style.height = 'auto'; // Reset height

            showTypingIndicator();

            try {
                const response = await fetch('<?php echo base_url('api/chatbot/ask') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        [csrfTokenName]: csrfTokenHash
                    },
                    body: JSON.stringify({
                        query: query
                    })
                });

                const data = await response.json();
                csrfTokenHash = data.csrf_token;

                removeTypingIndicator();

                if (response.ok) {
                    appendMessage('bot', data.response, data.interaction_id);
                } else {
                    appendMessage('bot', 'Maaf, terjadi kesalahan. Silakan coba lagi.');
                }
            } catch (error) {
                console.error('Error:', error);
                removeTypingIndicator();
                appendMessage('bot', 'Maaf, saya tidak dapat terhubung ke server.');
            }
        }

        // Fungsi untuk mengirim umpan balik
        async function sendFeedback(interactionId, helpful) {
            try {
                const response = await fetch('<?php echo base_url('api/chatbot/feedback') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        [csrfTokenName]: csrfTokenHash
                    },
                    body: JSON.stringify({
                        interaction_id: interactionId,
                        helpful: helpful
                    })
                });

                const data = await response.json();
                csrfTokenHash = data.csrf_token;

                if (!response.ok) {
                    console.error('Gagal mengirim umpan balik.');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Event listener untuk input textarea (auto-resize)
        userInput.addEventListener("input", () => {
            userInput.style.height = 'auto';
            userInput.style.height = `${userInput.scrollHeight}px`;
        });

        // Event listener untuk tombol kirim
        sendButton.addEventListener('click', sendMessage);
        userInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Event listener untuk tombol umpan balik (delegasi event)
        chatArea.addEventListener('click', function(e) {
            const button = e.target.closest('.feedback-btn');
            if (!button) return;

            const feedbackContainer = button.closest('.feedback-container');
            if (feedbackContainer.querySelector('.selected')) return; // Already voted

            const interactionId = feedbackContainer.dataset.interactionId;
            const isLike = button.classList.contains('like');

            const buttons = feedbackContainer.querySelectorAll('.feedback-btn');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.style.cursor = 'default';
            });
            button.classList.add('selected');

            sendFeedback(interactionId, isLike ? 1 : 0);
        });

        // Event listener untuk tombol contoh pertanyaan
        const exampleQuestionsContainer = document.querySelector('.example-questions');
        if (exampleQuestionsContainer) {
            exampleQuestionsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('example-question-btn')) {
                    const questionText = e.target.textContent;
                    userInput.value = questionText;
                    userInput.focus();
                    // Auto-resize textarea setelah mengatur nilainya
                    userInput.style.height = 'auto';
                    userInput.style.height = `${userInput.scrollHeight}px`;
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>