<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function createEvent(Request $request)
    {
        try {
            
            // Validate the input
            $request->validate([
                'event_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'event_date' => 'required', // Assuming 'event_date' is a required field
                // Add other validations for 'event_venue' and 'event_title' if needed
            ]);
    
            $filename = ""; // Initialize the filename variable
    
            if ($request->file('event_photo')) {
                $image = $request->file('event_photo');
                $path = public_path('/uploads/event_images/');
                !is_dir($path) &&
                    mkdir($path, 0777, true);
                $filename = time() . '.' . $image->extension();
                $image->move($path, $filename);
            }
    
            // Retrieve event_date from the request
            $event_date = $request->input('event_date');
            $event_venue = $request->input('event_venue');
            $event_title = $request->input('event_title');
    
            // Create the product record with the 'product_photo' field
            $event = Event::create([
                'event_photo' => $filename, // Assign the filename to the 'product_photo' field
                'event_date' => $event_date,
                'event_venue' => $event_venue,
                'event_title' => $event_title,
                'event_description' => $request->input('event_description'),
            ]);
                
            return response()->json([
                'status' => true,
                'message' => 'Event Added Successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
       // List the products
public function index()
{
    try {
        $events = Event::all(); // Retrieve all products from the database
        
        return response()->json([
            'status' => true,
            'message' => 'Events Found Successfully',
            'data' => $events
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}
// Delete a product
public function destroy($id)
{
    try {
        $event = Event::findOrFail($id); // Find the product by its ID
        
        // Delete the product image file from storage
        if (!empty($event->event_photo)) {
            $path = public_path('/uploads/event_images/') . $event->event_photo;
            if (file_exists($path)) {
                unlink($path); // Delete the image file
            }
        }

        // Delete the Event
        $event->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Event deleted successfully'
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}

}
