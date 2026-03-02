<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        .container { background: #f9f9f9; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { background: #4CAF50; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%; }
        button:hover { background: #45a049; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        .status { padding: 15px; border-radius: 5px; margin-top: 20px; display: none; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>💳 Payment Checkout</h1>
        
        <form id="paymentForm">
            <div class="form-group">
                <label for="amount">Amount (KES):</label>
                <input type="number" id="amount" value="10" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" placeholder="07XX XXX XXX" value="2547XXXXXXXX" required>
                <small>Format: 07XX XXX XXX or 2547XXXXXXXX</small>
            </div>
            
            <div class="form-group">
                <label for="provider">Payment Method:</label>
                <select id="provider" required>
                    <option value="mpesa">M-Pesa</option>
                    <option value="airtel">Airtel Money</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="orderRef">Order Reference:</label>
                <input type="text" id="orderRef" value="ORDER-<?php echo time(); ?>" readonly>
            </div>
            
            <button type="button" onclick="initiatePayment()" id="payBtn">Pay Now</button>
        </form>
        
        <div id="statusMessage" class="status info">
            <p id="statusText">Please check your phone to complete the payment...</p>
            <div id="statusDetails"></div>
        </div>
    </div>

    <script>
        async function initiatePayment() {
            const amount = document.getElementById('amount').value;
            const phone = document.getElementById('phone').value;
            const provider = document.getElementById('provider').value;
            const orderRef = document.getElementById('orderRef').value;
            
            const btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.innerHTML = 'Processing...';
            
            // Hide any previous status
            document.getElementById('statusMessage').style.display = 'none';
            
            try {
                const response = await fetch('/api/initiate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: amount,
                        phone_number: phone,
                        provider: provider,
                        order_reference: orderRef
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    document.getElementById('statusText').textContent = 
                        '✅ Payment request sent! Please check your phone and enter PIN.';
                    document.getElementById('statusMessage').className = 'status success';
                    document.getElementById('statusMessage').style.display = 'block';
                    
                    // Start polling for status
                    pollPaymentStatus(data.data.transaction_id);
                } else {
                    // Show error
                    document.getElementById('statusText').textContent = 
                        '❌ ' + (data.message || 'Payment failed');
                    document.getElementById('statusMessage').className = 'status error';
                    document.getElementById('statusMessage').style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = 'Try Again';
                }
                
            } catch (error) {
                document.getElementById('statusText').textContent = 
                    '❌ Network error: ' + error.message;
                document.getElementById('statusMessage').className = 'status error';
                document.getElementById('statusMessage').style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = 'Try Again';
            }
        }
        
        async function pollPaymentStatus(transactionId) {
            let attempts = 0;
            const maxAttempts = 40; // 2 minutes (40 * 3 seconds)
            
            const interval = setInterval(async () => {
                attempts++;
                
                if (attempts > maxAttempts) {
                    clearInterval(interval);
                    document.getElementById('statusText').textContent = 
                        '⏰ Payment timeout. Please check your phone or try again.';
                    document.getElementById('payBtn').disabled = false;
                    document.getElementById('payBtn').innerHTML = 'Try Again';
                    return;
                }
                
                try {
                    const response = await fetch(`/api/status.php?id=${transactionId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        const status = data.data.status;
                        document.getElementById('statusDetails').innerHTML = 
                            `<p>Status: <strong>${status.toUpperCase()}</strong></p>
                             <p>Transaction ID: ${data.data.transaction_id}</p>`;
                        
                        if (status === 'success') {
                            clearInterval(interval);
                            document.getElementById('statusText').textContent = 
                                '✅ Payment Successful! Thank you for your purchase.';
                            document.getElementById('statusMessage').className = 'status success';
                            
                            // Redirect after 3 seconds
                            setTimeout(() => {
                                window.location.href = 'success.html';
                            }, 3000);
                            
                        } else if (status === 'failed' || status === 'cancelled') {
                            clearInterval(interval);
                            document.getElementById('statusText').textContent = 
                                '❌ Payment failed. Please try again.';
                            document.getElementById('statusMessage').className = 'status error';
                            document.getElementById('payBtn').disabled = false;
                            document.getElementById('payBtn').innerHTML = 'Try Again';
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }, 3000); // Check every 3 seconds
        }
        
        // Auto-generate order reference on page load
        document.getElementById('orderRef').value = 'ORDER-' + Date.now();
    </script>
</body>
</html>