// mpesaController.js
const axios = require('axios');
const crypto = require('crypto');

class MpesaController {
    constructor() {
        this.consumerKey = process.env.MPESA_CONSUMER_KEY;
        this.consumerSecret = process.env.MPESA_CONSUMER_SECRET;
        this.shortCode = process.env.MPESA_SHORTCODE;
        this.passkey = process.env.MPESA_PASSKEY;
        this.callbackURL = process.env.MPESA_CALLBACK_URL;
        this.authToken = null;
        this.tokenExpiry = null;
    }

    // Get access token from Safaricom
    async getAccessToken() {
        if (this.authToken && this.tokenExpiry > Date.now()) {
            return this.authToken;
        }

        const auth = Buffer.from(`${this.consumerKey}:${this.consumerSecret}`).toString('base64');
        
        try {
            const response = await axios.get('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials', {
                headers: {
                    'Authorization': `Basic ${auth}`
                }
            });
            
            this.authToken = response.data.access_token;
            this.tokenExpiry = Date.now() + (response.data.expires_in * 1000) - 60000; // Subtract 1 minute
            return this.authToken;
        } catch (error) {
            console.error('Error getting M-Pesa token:', error);
            throw error;
        }
    }

    // Generate password for STK push
    generatePassword() {
        const timestamp = new Date().toISOString().replace(/[^0-9]/g, '').slice(0, -3);
        const password = Buffer.from(`${this.shortCode}${this.passkey}${timestamp}`).toString('base64');
        return { password, timestamp };
    }

    // Initiate STK Push (M-Pesa payment request)
    async initiateSTKPush(phoneNumber, amount, accountReference, transactionDesc) {
        try {
            const token = await this.getAccessToken();
            const { password, timestamp } = this.generatePassword();
            
            const requestData = {
                BusinessShortCode: this.shortCode,
                Password: password,
                Timestamp: timestamp,
                TransactionType: 'CustomerPayBillOnline',
                Amount: amount,
                PartyA: phoneNumber,
                PartyB: this.shortCode,
                PhoneNumber: phoneNumber,
                CallBackURL: this.callbackURL,
                AccountReference: accountReference,
                TransactionDesc: transactionDesc
            };

            const response = await axios.post(
                'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
                requestData,
                {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                }
            );

            return {
                success: true,
                checkoutRequestID: response.data.CheckoutRequestID,
                merchantRequestID: response.data.MerchantRequestID,
                customerMessage: response.data.CustomerMessage
            };

        } catch (error) {
            console.error('STK Push error:', error.response?.data || error.message);
            return {
                success: false,
                error: error.response?.data || error.message
            };
        }
    }

    // Handle M-Pesa callback (webhook)
    async handleCallback(callbackData) {
        try {
            const resultCode = callbackData.Body.stkCallback.ResultCode;
            const resultDesc = callbackData.Body.stkCallback.ResultDesc;
            const checkoutRequestID = callbackData.Body.stkCallback.CheckoutRequestID;
            
            if (resultCode === 0) {
                // Payment successful
                const metadata = callbackData.Body.stkCallback.CallbackMetadata.Item;
                
                const donationData = {
                    amount: metadata.find(item => item.Name === 'Amount').Value,
                    mpesaReceiptNumber: metadata.find(item => item.Name === 'MpesaReceiptNumber').Value,
                    transactionDate: metadata.find(item => item.Name === 'TransactionDate').Value,
                    phoneNumber: metadata.find(item => item.Name === 'PhoneNumber').Value,
                    status: 'completed',
                    checkoutRequestID: checkoutRequestID
                };

                // Save to database
                await this.saveDonation(donationData);
                
                // Send receipt email
                await this.sendReceipt(donationData);
                
                return { success: true, data: donationData };
            } else {
                // Payment failed
                console.error('M-Pesa payment failed:', resultDesc);
                return { success: false, error: resultDesc };
            }
        } catch (error) {
            console.error('Callback handling error:', error);
            return { success: false, error: error.message };
        }
    }
}

module.exports = MpesaController;