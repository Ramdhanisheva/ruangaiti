<div class="widget newsletter-widget">
    <div class="widget-title">Hubungi Kami</div>
    <p style="font-size: 13px; color: var(--text-muted); margin: 10px 0 14px; line-height: 1.6;">
        Ada pertanyaan atau penawaran kerjasama? Kirimkan pesan Anda di bawah ini!
    </p>
    <form id="newsletter-ajax-form" action="{{ route('frontend.newsletter.subscribe') }}" method="POST">
        @csrf
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <input type="email" name="email" id="newsletter-ajax-email" required placeholder="Email kamu *" class="form-control" style="font-size:13px; padding:10px 12px; border-radius:var(--radius-button); border:1px solid var(--border-color); background:var(--bg-secondary); color:var(--text-primary); width:100%; box-sizing:border-box; outline:none;" aria-label="Masukkan email kamu">
            
            <textarea name="message" id="newsletter-ajax-message" required placeholder="Pesan kamu *" class="form-control" rows="3" style="font-size:13px; padding:10px 12px; border-radius:var(--radius-button); border:1px solid var(--border-color); background:var(--bg-secondary); color:var(--text-primary); width:100%; box-sizing:border-box; outline:none; resize:none;" aria-label="Masukkan pesan kamu"></textarea>
            
            <button type="submit" id="newsletter-ajax-btn" class="btn-primary" style="display:flex; align-items:center; justify-content:center; gap:8px; padding:10px 16px; font-size:13px; font-weight:600; width:100%; box-sizing:border-box; border-radius:var(--radius-button); border:none; cursor:pointer;" aria-label="Tombol kirim pesan">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                <span id="newsletter-ajax-btn-text">Kirim Pesan</span>
            </button>
        </div>
        <div id="newsletter-ajax-msg" style="margin-top: 10px; font-size: 12px; display: none; line-height: 1.4;"></div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('newsletter-ajax-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const emailInput = document.getElementById('newsletter-ajax-email');
                const messageInput = document.getElementById('newsletter-ajax-message');
                const btn = document.getElementById('newsletter-ajax-btn');
                const btnText = document.getElementById('newsletter-ajax-btn-text');
                const msg = document.getElementById('newsletter-ajax-msg');
                
                if (!emailInput.value || !messageInput.value) return;
                
                btn.disabled = true;
                btnText.textContent = 'Mengirim...';
                msg.style.display = 'none';
                
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        email: emailInput.value,
                        message: messageInput.value
                    })
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    btn.disabled = false;
                    btnText.textContent = 'Kirim Pesan';
                    msg.style.display = 'block';
                    if (res.status === 200) {
                        msg.style.color = '#10B981'; // Success green
                        msg.textContent = res.body.message || 'Sukses mengirim pesan!';
                        form.reset();
                    } else {
                        msg.style.color = '#EF4444'; // Error red
                        msg.textContent = res.body.errors?.email?.[0] || res.body.errors?.message?.[0] || res.body.message || 'Terjadi kesalahan.';
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btnText.textContent = 'Kirim Pesan';
                    msg.style.display = 'block';
                    msg.style.color = '#EF4444';
                    msg.textContent = 'Terjadi kesalahan koneksi.';
                });
            });
        }
    });
</script>
