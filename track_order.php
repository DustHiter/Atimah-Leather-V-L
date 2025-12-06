<?php
$page_title = "پیگیری سفارش";
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="track-container">
        <h1><i class="ri-search-eye-line me-2"></i>پیگیری سفارش</h1>
        <p>کد رهگیری سفارش خود را برای مشاهده جزئیات وارد کنید.</p>
        
        <form id="track-order-form" class="mt-4">
            <div class="mb-3">
                <input type="text" id="tracking_id" name="tracking_id" class="form-control" placeholder="کد رهگیری سفارش" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary"><i class="ri-search-line me-2"></i>جستجو</button>
            </div>
        </form>

        <div id="result-message" class="mt-3"></div>
    </div>
</div>

<!-- The Modal -->
<div id="order-modal" class="order-modal">
    <div class="order-modal-content">
        <span class="order-modal-close-btn">&times;</span>
        <div id="modal-body">
            <!-- Order details will be injected here by JavaScript -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('track-order-form');
    const modal = document.getElementById('order-modal');
    const modalBody = document.getElementById('modal-body');
    const closeBtn = document.querySelector('.order-modal-close-btn');
    const resultMessage = document.getElementById('result-message');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const trackingId = document.getElementById('tracking_id').value;
        
        resultMessage.innerHTML = `<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> در حال جستجو...`;
        resultMessage.className = 'text-muted';

        try {
            const response = await fetch('api/get_order_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ tracking_id: trackingId }),
            });

            if (!response.ok) {
                throw new Error(`خطای سرور: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                resultMessage.textContent = '';
                displayOrderDetails(data.order, data.products);
                modal.style.display = 'block';
            } else {
                resultMessage.textContent = data.message;
                resultMessage.className = 'text-danger fw-bold';
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            resultMessage.textContent = 'خطا در برقراری ارتباط با سرور.';
            resultMessage.className = 'text-danger fw-bold';
        }
    });

    function displayOrderDetails(order, products) {
        let productsHtml = `
            <div class="detail-box" style="grid-column: 1 / -1;">
                <h3>محصولات سفارش</h3>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>محصول</th>
                            <th>تعداد</th>
                            <th>رنگ</th>
                            <th>قیمت واحد</th>
                            <th>قیمت کل</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        products.forEach(p => {
            productsHtml += `
                <tr>
                    <td data-label="محصول"><img src="${p.image_url}" alt="${p.name}" class="me-2">${p.name}</td>
                    <td data-label="تعداد">${p.quantity}</td>
                    <td data-label="رنگ"><span class="cart-item-color-swatch" style="background-color: ${p.color || 'transparent'}"></span></td>
                    <td data-label="قیمت واحد">${parseInt(p.price).toLocaleString()} تومان</td>
                    <td data-label="قیمت کل">${(p.quantity * p.price).toLocaleString()} تومان</td>
                </tr>
            `;
        });
        productsHtml += `</tbody></table></div>`;

        modalBody.innerHTML = `
            <div class="order-modal-header">
                <h2>جزئیات سفارش</h2>
                <p class="text-muted">کد رهگیری: ${order.tracking_id}</p>
            </div>
            <div class="order-details-grid">
                <div class="detail-box">
                    <h3>اطلاعات خریدار</h3>
                    <p><strong>نام:</strong> ${order.full_name}</p>
                    <p><strong>ایمیل:</strong> ${order.email}</p>
                    <p><strong>تلفن:</strong> ${order.billing_phone}</p>
                </div>
                <div class="detail-box">
                    <h3>اطلاعات سفارش</h3>
                    <p><strong>وضعیت:</strong> <span class="order-status status-${order.status}">${order.status_jalali}</span></p>
                    <p><strong>تاریخ ثبت:</strong> ${order.created_at_jalali}</p>
                    <p><strong>آدرس:</strong> ${order.address}</p>
                </div>
                ${productsHtml}
            </div>
            <div class="summary-totals mt-4 text-center">
                 <div class="grand-total">
                    <span class="label">جمع کل: </span>
                    <span class="value">${parseInt(order.total_amount).toLocaleString()} تومان</span>
                </div>
            </div>
        `;
    }

    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
