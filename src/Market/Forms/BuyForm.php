<?php

namespace Market\Forms;

use Market\Main;
use Market\FormAPI\CustomForm;
use Market\FormAPI\SimpleForm;
use pocketmine\player\Player;

class BuyForm {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    
    /**
     * Kategori seçim menüsünü göster
     */
    public function sendForm(Player $player, ?string $selectedCategory = null): void {
        $languageManager = $this->plugin->getLanguageManager();
        $marketManager = $this->plugin->getMarketManager();
        $categories = $marketManager->getCategories($player);
        
        // Eğer kategori seçilmişse, o kategorideki itemleri göster
        if ($selectedCategory !== null) {
            $this->showCategoryItems($player, $selectedCategory);
            return;
        }
        
        // Kategori seçim menüsü
        $plugin = $this->plugin;
        $categoryKeys = array_keys($categories);
        $form = new SimpleForm(function (Player $player, $data) use ($plugin, $categories, $categoryKeys) {
            if ($data === null) {
                return;
            }
            
            if (!$player->isOnline()) {
                return;
            }
            
            // $data label (kategori key) olarak geliyor, ama integer da olabilir
            $selectedCategory = null;
            
            // Eğer string ise direkt kullan
            if (is_string($data) && isset($categories[$data])) {
                $selectedCategory = $data;
            }
            // Eğer integer ise (label null olduğu için), array'den al
            elseif (is_int($data) && isset($categoryKeys[$data])) {
                $selectedCategory = $categoryKeys[$data];
            }
            
            if ($selectedCategory === null) {
                $plugin->getLogger()->warning("BuyForm: Geçersiz kategori seçimi! Data: " . var_export($data, true));
                return;
            }
            
            $buyForm = new BuyForm($plugin);
            $buyForm->sendForm($player, $selectedCategory);
        });
        
        $form->setTitle($languageManager->get("buy.title", $player) . $languageManager->get("categories.category_title", $player));
        $form->setContent($languageManager->get("categories.select_category", $player));
        
        foreach ($categories as $categoryKey => $categoryName) {
            $itemCount = count($marketManager->getItemsByCategory($categoryKey === "all" ? null : $categoryKey));
            $itemsCountText = $languageManager->get("categories.items_count", $player, ["count" => $itemCount]);
            $form->addButton("§e{$categoryName}\n§7{$itemsCountText}", SimpleForm::IMAGE_TYPE_PATH, "textures/items/book_normal", $categoryKey);
        }
        
        $player->sendForm($form);
    }
    
    /**
     * Seçilen kategorideki itemleri göster
     */
    private function showCategoryItems(Player $player, string $category): void {
        $marketManager = $this->plugin->getMarketManager();
        $languageManager = $this->plugin->getLanguageManager();
        $categories = $marketManager->getCategories($player);
        $categoryName = $categories[$category] ?? $category;
        
        // Kategoriye göre itemleri al
        if ($category === "all") {
            $items = $marketManager->getAllItems();
        } else {
            $items = $marketManager->getItemsByCategory($category);
        }
        
        $itemNames = [];
        $itemIds = [];
        
        $config = $this->plugin->getMarketConfig();
        $settings = $config->get("settings", []);
        $currency = $settings["currency"] ?? "Para";
        
        foreach ($items as $itemId => $itemData) {
            $name = $itemData["name"] ?? $itemId;
            $buyPrice = $itemData["buy"] ?? 0;
            $itemNames[] = "§e§l{$name} §r§7({$itemId}) §8- §a§l{$buyPrice} {$currency}";
            $itemIds[] = $itemId;
        }
        
        $languageManager = $this->plugin->getLanguageManager();
        
        if (empty($itemNames)) {
            $player->sendMessage($languageManager->get("buy.no_items", $player));
            // Kategori seçim menüsüne geri dön
            $this->sendForm($player);
            return;
        }
        
        $plugin = $this->plugin;
        $form = new CustomForm(function (Player $player, ?array $response) use ($itemIds, $plugin, $category) {
            if ($response === null) {
                return;
            }
            
            // Player online kontrolü
            if (!$player->isOnline()) {
                return;
            }
            
            // Response validasyonu
            if (!isset($response["item"]) || !isset($response["amount"])) {
                return;
            }
            
            $selectedIndex = (int) $response["item"];
            $amount = $response["amount"];
            
            $langManager = $plugin->getLanguageManager();
            
            // Array bounds kontrolü
            if ($selectedIndex < 0 || !isset($itemIds[$selectedIndex])) {
                $player->sendMessage($langManager->get("buy.invalid_selection", $player));
                return;
            }
            
            $itemId = $itemIds[$selectedIndex];
            
            // Amount validasyonu - sadece sayı kabul et
            if (!is_numeric($amount)) {
                $player->sendMessage($langManager->get("buy.invalid_amount", $player));
                return;
            }
            
            $amount = (int) $amount;
            
            // Negatif ve sıfır kontrolü
            if ($amount <= 0) {
                $player->sendMessage($langManager->get("buy.amount_too_low", $player));
                return;
            }
            
            // Integer overflow kontrolü
            if ($amount > PHP_INT_MAX / 1000) {
                $player->sendMessage($langManager->get("buy.amount_too_high", $player));
                return;
            }
            
            $plugin->getMarketManager()->buyItem($player, $itemId, $amount);
        });
        
        $form->setTitle($languageManager->get("buy.title", $player) . " - {$categoryName}");
        $form->addLabel($languageManager->get("buy.content", $player) . "\n§7Kategori: §e{$categoryName}");
        $form->addDropdown($languageManager->get("buy.item_dropdown", $player), $itemNames, 0, "item");
        $form->addInput($languageManager->get("buy.amount_input", $player), $languageManager->get("buy.amount_placeholder", $player), "1", "amount");
        
        $player->sendForm($form);
    }
}
