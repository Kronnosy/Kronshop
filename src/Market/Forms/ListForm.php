<?php

namespace Market\Forms;

use Market\Main;
use Market\FormAPI\SimpleForm;
use pocketmine\player\Player;

class ListForm {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    
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
                // Ana menüye dön
                $menuForm = new MarketMenuForm($plugin);
                $menuForm->sendForm($player);
                return;
            }
            
            if (!$player->isOnline()) {
                return;
            }
            
            // "back" butonu kontrolü
            if ($data === "back" || (is_string($data) && $data === "back")) {
                $menuForm = new MarketMenuForm($plugin);
                $menuForm->sendForm($player);
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
                $plugin->getLogger()->warning("ListForm: Geçersiz kategori seçimi! Data: " . var_export($data, true));
                return;
            }
            
            $listForm = new ListForm($plugin);
            $listForm->sendForm($player, $selectedCategory);
        });
        
        $form->setTitle($languageManager->get("list.title", $player) . $languageManager->get("categories.category_title", $player));
        $form->setContent($languageManager->get("categories.select_category", $player));
        
        foreach ($categories as $categoryKey => $categoryName) {
            $itemCount = count($marketManager->getItemsByCategory($categoryKey === "all" ? null : $categoryKey));
            $itemsCountText = $languageManager->get("categories.items_count", $player, ["count" => $itemCount]);
            $form->addButton("§e{$categoryName}\n§7{$itemsCountText}", SimpleForm::IMAGE_TYPE_PATH, "textures/items/book_normal", $categoryKey);
        }
        
        $form->addButton($languageManager->get("list.back_button", $player), SimpleForm::IMAGE_TYPE_PATH, "textures/blocks/barrier", "back");
        
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
        
        $config = $this->plugin->getMarketConfig();
        $settings = $config->get("settings", []);
        $currency = $settings["currency"] ?? "Para";
        $languageManager = $this->plugin->getLanguageManager();
        
        $plugin = $this->plugin;
        $form = new SimpleForm(function (Player $player, $data) use ($plugin, $category) {
            if ($data === null) {
                // Kategori seçim menüsüne geri dön
                $listForm = new ListForm($plugin);
                $listForm->sendForm($player);
            }
        });
        
        $form->setTitle($languageManager->get("list.title", $player) . " - {$categoryName}");
        
        $content = "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $content .= "§7Kategori: §e{$categoryName}\n";
        $content .= "§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        if (empty($items)) {
            $content .= $languageManager->get("list.no_items", $player);
        } else {
            $index = 1;
            foreach ($items as $itemId => $itemData) {
                $name = $itemData["name"] ?? $itemId;
                $buyPrice = $itemData["buy"] ?? 0;
                $sellPrice = $itemData["sell"] ?? 0;
                
                $itemFormat = $languageManager->get("list.item_format", $player, [
                    "index" => $index,
                    "name" => $name,
                    "item_id" => $itemId,
                    "buy_price" => $buyPrice,
                    "sell_price" => $sellPrice,
                    "currency" => $currency
                ]);
                $content .= $itemFormat;
                $index++;
            }
        }
        
        $content .= "§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        $form->setContent($content);
        $form->addButton($languageManager->get("list.back_button", $player), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/back_button", "back");
        
        $player->sendForm($form);
    }
}
