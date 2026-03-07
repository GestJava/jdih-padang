<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class DebugData extends Controller
{
    public function index()
    {
        echo "<h1>JDIH Data Debug</h1>";

        // Test WebPeraturanModel
        echo "<h2>Testing WebPeraturanModel</h2>";
        try {
            $webPeraturanModel = new \App\Models\WebPeraturanModel();
            $latest_peraturan = $webPeraturanModel->getLatestPeraturan(8);

            echo "<p>Latest Peraturan Count: " . count($latest_peraturan) . "</p>";

            if (!empty($latest_peraturan)) {
                echo "<h3>Sample Data:</h3>";
                echo "<pre>";
                print_r(array_slice($latest_peraturan, 0, 2)); // Show first 2 items
                echo "</pre>";
            } else {
                echo "<p style='color: red;'>No Peraturan Data Found!</p>";
            }
        } catch (\Exception $e) {
            echo "<p style='color: red;'>WebPeraturanModel Error: " . $e->getMessage() . "</p>";
        }

        // Test BeritaModel
        echo "<h2>Testing BeritaModel</h2>";
        try {
            $beritaModel = new \App\Models\BeritaModel();
            $latest_berita = $beritaModel->getLatestBerita(3);

            echo "<p>Latest Berita Count: " . count($latest_berita) . "</p>";

            if (!empty($latest_berita)) {
                echo "<h3>Sample Data:</h3>";
                echo "<pre>";
                print_r(array_slice($latest_berita, 0, 2)); // Show first 2 items
                echo "</pre>";
            } else {
                echo "<p style='color: red;'>No Berita Data Found!</p>";
            }
        } catch (\Exception $e) {
            echo "<p style='color: red;'>BeritaModel Error: " . $e->getMessage() . "</p>";
        }

        // Test Database Connection
        echo "<h2>Testing Database Connection</h2>";
        try {
            $db = \Config\Database::connect();
            $query = $db->query("SELECT COUNT(*) as total FROM web_peraturan");
            $result = $query->getRow();
            echo "<p>Total Peraturan in DB: " . $result->total . "</p>";

            $query = $db->query("SELECT COUNT(*) as total FROM web_berita");
            $result = $query->getRow();
            echo "<p>Total Berita in DB: " . $result->total . "</p>";
        } catch (\Exception $e) {
            echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
        }

        echo "<p><a href='" . base_url() . "'>Back to Home</a></p>";
    }
}
