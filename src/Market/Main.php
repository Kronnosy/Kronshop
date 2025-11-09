<?php

namespace Market;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use Market\Commands\MarketCommand;
use Kronnonmy\Kronnomy;

class Main extends PluginBase implements Listener {
    
    private Config $config;
    private MarketManager $marketManager;
    private LanguageManager $languageManager;
    private ?Kronnomy $kronnomy = null;
    private ?\pocketmine\plugin\Plugin $bedrockEconomy = null;
    
    public function onEnable(): void {
        $this->saveResource("config.json");
        $this->config = new Config($this->getDataFolder() . "config.json", Config::JSON);
        
        // LanguageManager'ı önce yükle
        $this->languageManager = new LanguageManager($this);
        
        // Kronnomy kontrolü (öncelikli)
        $kronnomyPlugin = $this->getServer()->getPluginManager()->getPlugin("Kronnomy");
        if ($kronnomyPlugin instanceof Kronnomy) {
            $this->kronnomy = $kronnomyPlugin;
            $this->getLogger()->info($this->languageManager->get("main.kronnomy_connected"));
        } else {
            // BedrockEconomy kontrolü (yedek)
            $bedrockEconomyPlugin = $this->getServer()->getPluginManager()->getPlugin("BedrockEconomy");
            if ($bedrockEconomyPlugin === null) {
                $this->getLogger()->warning($this->languageManager->get("main.no_economy"));
                $this->getLogger()->warning($this->languageManager->get("main.no_economy_warning"));
            } else {
                $this->bedrockEconomy = $bedrockEconomyPlugin;
                $this->getLogger()->info($this->languageManager->get("main.bedrockeconomy_connected"));
            }
        }
        
        $this->marketManager = new MarketManager($this);
        
        // Event listener'ları kaydet
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        // Ana market komutu (alt komutları da handle eder)
        $commandMap = $this->getServer()->getCommandMap();
        $command = new MarketCommand($this);
        
        // Eğer komut zaten kayıtlıysa önce kaldır
        $existingCommand = $commandMap->getCommand("market");
        if ($existingCommand !== null) {
            $commandMap->unregister($existingCommand);
        }
        
        $commandMap->register("market", $command);
        
        $this->getLogger()->info($this->languageManager->get("main.plugin_enabled"));
    }
    
    /**
     * Player disconnect olduğunda transaction lock'ları temizle
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $this->marketManager->clearTransactionLock($player->getName());
    }
    
    public function onDisable(): void {
        // Plugin kapanırken tüm transaction lock'ları temizle
        // (MarketManager'da private olduğu için burada yapamıyoruz ama
        //  player quit event'leri zaten temizliyor)
    }
    
    public function getKronnomy(): ?Kronnomy {
        return $this->kronnomy;
    }
    
    public function getBedrockEconomy() {
        return $this->bedrockEconomy;
    }
    
    public function isKronnomyEnabled(): bool {
        return $this->kronnomy !== null;
    }
    
    public function isBedrockEconomyEnabled(): bool {
        return $this->bedrockEconomy !== null;
    }
    
    public function isEconomyEnabled(): bool {
        return $this->isKronnomyEnabled() || $this->isBedrockEconomyEnabled();
    }
    
    public function getMarketConfig(): Config {
        return $this->config;
    }
    
    public function getMarketManager(): MarketManager {
        return $this->marketManager;
    }
    
    public function getLanguageManager(): LanguageManager {
        return $this->languageManager;
    }
    
    public function reloadConfig(): void {
        $this->config->reload();
        $this->getLogger()->info($this->languageManager->get("main.config_reloaded"));
    }
}

