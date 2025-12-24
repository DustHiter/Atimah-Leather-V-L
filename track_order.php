<?php
$page_title = "پیگیری سفارش";
include 'includes/header.php';
?>

<div class="container section-padding">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-body p-5">
                    <h1 class="text-center"><i class="ri-search-eye-line me-2"></i>پیگیری سفارش</h1>
                    <p class="text-center text-muted">کد رهگیری سفارش خود را برای مشاهده جزئیات وارد کنید.</p>
                    
                    <form id="track-order-form" class="mt-4">
                        <div class="mb-3">
                            <input type="text" id="tracking_id" name="tracking_id" class="form-control form-control-lg" placeholder="کد رهگیری سفارش" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="ri-search-line me-2"></i>جستجو</button>
                        </div>
                    </form>

                    <div id="result-message" class="mt-4 text-center"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Tracking Modal -->
<div class="tracking-modal-container" id="tracking-modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>جزئیات سفارش <span id="modal-order-id"></span></h3>
            <button class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="order-summary">
                <div class="detail-item"><strong>تاریخ ثبت:</strong> <span id="modal-order-date"></span></div>
                <div class="detail-item"><strong>مبلغ کل:</strong> <span id="modal-order-amount"></span></div>
                <div class="detail-item"><strong>تخفیف:</strong> <span id="modal-order-discount"></span></div>
            </div>
            <div class="status-details">
                <h4>وضعیت سفارش: <span id="modal-order-status-text" style="font-weight: bold;"></span></h4>
                <div class="status-tracker" id="modal-status-tracker">
                    <div class="status-progress"></div>
                    <div class="status-step" data-status="placed">
                        <div class="dot"></div><span class="label">ثبت سفارش</span>
                    </div>
                    <div class="status-step" data-status="processing">
                        <div class="dot"></div><span class="label">در حال پردازش</span>
                    </div>
                    <div class="status-step" data-status="shipped">
                        <div class="dot"></div><span class="label">ارسال شده</span>
                    </div>
                    <div class="status-step" data-status="completed">
                        <div class="dot"></div><span class="label">تحویل شده</span>
                    </div>
                </div>
            </div>
            <div class="shipping-details">
                <h4>اطلاعات ارسال</h4>
                <div class="detail-item"><strong>تحویل گیرنده:</strong> <span id="modal-shipping-name"></span></div>
                <div class="detail-item"><strong>آدرس:</strong> <span id="modal-shipping-address"></span></div>
                <div class="detail-item"><strong>کدپستی:</strong> <span id="modal-shipping-postal-code"></span></div>
            </div>
            <div class="products-list">
                <h4>محصولات سفارش</h4>
                <div id="modal-products-list"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('track-order-form');
    const modal = document.getElementById('tracking-modal');
    const overlay = document.querySelector('.modal-overlay');
    const closeBtn = document.querySelector('.modal-close-btn');
    const resultMessage = document.getElementById('result-message');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const trackingId = document.getElementById('tracking_id').value;
        
        resultMessage.innerHTML = `<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> در حال جستجو...`;
        resultMessage.className = 'text-center text-muted';

        try {
            const response = await fetch('api/get_order_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tracking_id: trackingId }),
            });

            const data = await response.json();

            if (data.success) {
                resultMessage.innerHTML = '';
                displayOrderDetails(data.order, data.products);
                modal.classList.add('visible');
            } else {
                resultMessage.innerHTML = data.message;
                resultMessage.className = 'text-center text-danger fw-bold';
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            resultMessage.innerHTML = 'خطا در برقراری ارتباط با سرور. لطفاً اتصال اینترنت خود را بررسی کنید.';
            resultMessage.className = 'text-center text-danger fw-bold';
        }
    });

    function displayOrderDetails(order, products) {
        document.getElementById('modal-order-id').textContent = '#' + order.id;
        document.getElementById('modal-order-date').textContent = order.order_date;
        document.getElementById('modal-order-amount').textContent = order.total_amount;
        document.getElementById('modal-order-discount').textContent = order.discount_amount;
        
        document.getElementById('modal-shipping-name').textContent = order.shipping_name;
        document.getElementById('modal-shipping-address').textContent = order.shipping_address;
        document.getElementById('modal-shipping-postal-code').textContent = order.shipping_postal_code;

        const productsContainer = document.getElementById('modal-products-list');
        productsContainer.innerHTML = '';
        if (products && products.length > 0) {
            products.forEach(p => {
                const imageUrl = p.image_url ? p.image_url : 'assets/images/placeholder.png';
                productsContainer.innerHTML += `
                    <div class="product-item">
                        <img src="${imageUrl}" alt="${p.name}" onerror="this.onerror=null;this.src='assets/images/placeholder.png';">
                        <div class="product-info">
                            <span class="product-name">${p.name}</span>
                            <div class="product-meta">
                                <span class="product-quantity">تعداد: ${p.quantity}</span>
                                ${p.color ? `
                                <span class="product-color-wrapper">
                                    رنگ: <span class="product-color-dot" style="background-color: ${p.color};"></span>
                                </span>` : ''}
                            </div>
                        </div>
                        <div class="product-price">${p.price}</div>
                    </div>
                `;
            });
        } else {
            productsContainer.innerHTML = '<p class="text-center text-muted">محصولی برای این سفارش یافت نشد.</p>';
        }
        
        updateStatusTracker(order.status, order.status_persian);
    }

    function updateStatusTracker(status, statusPersian) {
        console.log('--- Debugging Status ---');
        console.log('Received status:', status);

        const statusTextEl = document.getElementById('modal-order-status-text');
        const tracker = document.getElementById('modal-status-tracker');
        const progress = tracker.querySelector('.status-progress');
        const steps = Array.from(tracker.querySelectorAll('.status-step'));

        // 1. Reset all dynamic styles and classes
        steps.forEach(step => {
            step.classList.remove('active', 'completed');
            const dot = step.querySelector('.dot');
            if (dot) dot.style.backgroundColor = ''; // Reset to default CSS color
        });
        tracker.classList.remove('is-cancelled');
        progress.style.width = '0%';
        progress.style.backgroundColor = ''; // Reset to default CSS color

        // 2. Map API status to internal status keys
        const statusKeyMap = {
            'pending': 'placed',
            'processing': 'processing',
            'shipped': 'shipped',
            'delivered': 'completed',
            'completed': 'completed',
            'cancelled': 'cancelled'
        };
        const mappedStatus = status ? statusKeyMap[status.toLowerCase()] : 'placed';

        // 3. Define display properties for each status, using CSS variables
        const statusDisplayMap = {
            'placed':     { text: 'ثبت شده',        colorVar: '--status-default-dark', progress: '0%',   stepIndex: 0 },
            'processing': { text: 'در حال پردازش',  colorVar: '--status-processing',   progress: '33%',  stepIndex: 1 },
            'shipped':    { text: 'ارسال شده',      colorVar: '--status-shipped',      progress: '66%',  stepIndex: 2 },
            'completed':  { text: 'تحویل شده',    colorVar: '--status-completed',    progress: '100%', stepIndex: 3 },
            'cancelled':  { text: 'لغو شده',       colorVar: '--status-cancelled',    progress: '0%',   stepIndex: -1 }
        };

        const displayInfo = statusDisplayMap[mappedStatus] || statusDisplayMap['placed'];
        const currentStatusColor = `var(${displayInfo.colorVar})`;
        const completedColor = `var(${statusDisplayMap['completed'].colorVar})`;
        const cancelledColor = `var(${statusDisplayMap['cancelled'].colorVar})`;

        console.log(`Mapped status: ${mappedStatus}, Index: ${displayInfo.stepIndex}`);

        // 4. Update main status text color and content
        statusTextEl.textContent = statusPersian || displayInfo.text;
        statusTextEl.style.color = currentStatusColor;

        // 5. Handle the special 'cancelled' state
        if (mappedStatus === 'cancelled') {
            tracker.classList.add('is-cancelled');
            progress.style.backgroundColor = cancelledColor;
            progress.style.width = '100%';
            steps.forEach(s => {
                const dot = s.querySelector('.dot');
                if (dot) dot.style.backgroundColor = cancelledColor;
            });
        } else {
            // 6. Handle normal order progression
            progress.style.backgroundColor = completedColor; // Progress bar is always green for consistency
            
            setTimeout(() => {
                progress.style.width = displayInfo.progress;
            }, 100);

            // Update step classes and dot colors
            if (displayInfo.stepIndex >= 0) {
                // Mark all past steps as completed (green)
                for (let i = 0; i < displayInfo.stepIndex; i++) {
                    if (steps[i]) {
                        steps[i].classList.add('completed');
                        const dot = steps[i].querySelector('.dot');
                        if (dot) dot.style.backgroundColor = completedColor;
                    }
                }
                // Mark current step as active (yellow, blue, or green)
                if (steps[displayInfo.stepIndex]) {
                    steps[displayInfo.stepIndex].classList.add('active');
                    const dot = steps[displayInfo.stepIndex].querySelector('.dot');
                    if (dot) dot.style.backgroundColor = currentStatusColor;
                }
            }
        }
    }

    function closeModal() {
        modal.classList.remove('visible');
    }

    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);
});
</script>
<?php include 'includes/footer.php'; ?>