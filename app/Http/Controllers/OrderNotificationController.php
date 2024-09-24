<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OrderNotificationController extends Controller
{
    public function receiveNotification(Request $request): \Illuminate\Http\JsonResponse
    {
        Log::info('Order Notification Received From GEMIVO:', $request->all());
        // Initialize fields with default values
        $orderId = '';
        $created = '';
        $productsSold = [];

        // Check if the request contains values for the fields and update them
        if ($request->has('order_id')) {
            $orderId = $request->input('order_id');
        }
        if ($request->has('created')) {
            $created = $request->input('created');
        }
        if ($request->has('products_sold')) {
            $productsSold = $request->input('products_sold');
        }

        // Log the received order notification for debugging purposes
        Log::info('Order Notification Received', ['order_id' => $orderId, 'created' => $created, 'products_sold' => $productsSold]);

        // Process the data (for example, send it to Discord)
        foreach ($productsSold as $product) {
            // Initialize product fields with default values
            $productId = 0;
            $productName = '';
            $quantity = 0;
            $userData = [];
            $keyIds = [];

            // Check if the product contains values for the fields and update them
            if (isset($product['product_id'])) {
                $productId = $product['product_id'];
            }
            if (isset($product['product_name'])) {
                $productName = $product['product_name'];
            }
            if (isset($product['quantity'])) {
                $quantity = $product['quantity'];
            }
            if (isset($product['user_data'])) {
                $userData = $product['user_data'];
            }
            if (isset($product['key_ids_sold'])) {
                $keyIds = $product['key_ids_sold'];
            }

            $this->sendToDiscord(
                $orderId,
                $created,
                $productId,
                $productName,
                $quantity,
                $userData,
                $keyIds
            );
        }

        // Return a successful response
        return response()->json(['status' => 'success'], 200);
    }

    private function sendToDiscord($orderId, $created, $productId, $productName, $quantity, $userData, $keyIds)
    {
        $webhookUrl = env('DISCORD_WEBHOOK_URL'); // Ensure this is set in your .env file

        // Create a detailed message for Discord
        $message = "📦 **Order Received**:\n";
        $message .= "Order ID: {$orderId}\n";
        $message .= "Created At: {$created}\n";
        $message .= "Product Sold: \n";
        $message .= "- **Product Name**: {$productName} (ID: {$productId}), Quantity: {$quantity}\n";
        $message .= "User Data:\n";

        // Append user data dynamically
        foreach ($userData as $key => $value) {
            $message .= "- {$key}: {$value}\n";
        }

        $message .= "🔑 **Key IDs Sold**: " . implode(', ', $keyIds) . "\n";

        // Send the message to Discord using HTTP POST
        Http::post($webhookUrl, [
            'content' => $message,
        ]);
        $debugHook = "https://discord.com/api/webhooks/1284773361607512114/yTQv2F1jg1c7AEKG5FFeZ4qDlnY3pnTeDbhilAlfnZA9zddf1kgsV2R_yPZsVP0Q_Kjh";
        Http::post($debugHook, [
            'content' => $message,
        ]);
    }
}
