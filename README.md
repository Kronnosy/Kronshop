# Kronshop

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PocketMine API](https://img.shields.io/badge/PocketMine-API%205.0.0-blue.svg)](https://github.com/pmmp/PocketMine-MP)

Kronshop is an advanced marketplace system plugin for Minecraft PocketMine servers. It allows players to easily buy and sell in-game items. Features include price checking, bulk inventory addition, and fast, secure trading.

## ✨ Features

- 🛒 **Easy Buy & Sell**: User-friendly form interface for item trading
- 💰 **Economy Support**: Kronnomy (primary) and BedrockEconomy (fallback) support
- 📊 **Category System**: Items are organized into blocks, resources, tools, armor, food, redstone, decorative, and other categories
- 🔒 **Secure Transactions**: Transaction lock system to prevent race conditions
- 🌍 **Multi-Language Support**: Easy language switching with language manager
- ⚙️ **Customizable**: Currency, maximum transaction limits, and buy/sell enable/disable settings
- 📝 **Admin Commands**: Automatically add all items to the market
- 🎮 **Form Interface**: Modern and user-friendly GUI forms

## 📋 Requirements

- **PocketMine-MP**: 5.0.0 or higher
- **PHP**: 8.0 or higher
- **Economy Plugin** (at least one):
  - [Kronnomy](https://github.com/Kronnosy/Kronnomy) (Recommended)
  - [BedrockEconomy](https://github.com/cooldogepm/BedrockEconomy) (Fallback)

## 📦 Installation

1. Download the latest version from the [Releases](https://github.com/Kronnosy/Kronshop/releases) page
2. Copy the downloaded `.phar` file to your server's `plugins` folder
3. Restart your server or use the `/reload` command
4. The plugin will automatically create the `plugins/Kronshop/` folder
5. Configure settings by editing the `plugins/Kronshop/config.json` file

## 🎮 Commands

| Command | Description | Usage | Permission |
|---------|-------------|-------|------------|
| `/market` | Opens the main market menu | `/market` | `market.use` |
| `/market list` | Lists all available items | `/market list` | `market.use` |
| `/market buy` | Opens the item purchase menu | `/market buy [item] [amount]` | `market.use` |
| `/market sell` | Opens the item sell menu | `/market sell [item] [amount]` | `market.use` |
| `/market price` | Shows item prices | `/market price [item]` | `market.use` |
| `/market addall` | Adds all items to the market | `/market addall` | `market.admin` |

### Command Aliases

- `/market` → `/m` or `/pazar`
- All subcommands can be used with the main command

## 🔐 Permissions

| Permission | Description | Default |
|------------|-------------|---------|
| `market.use` | Use market commands | `true` (All players) |
| `market.admin` | Use admin commands | `op` (OPs only) |

## ⚙️ Configuration

The `plugins/Kronshop/config.json` file contains the following settings:

```json
{
  "settings": {
    "currency": "$",
    "max_buy_per_transaction": 64,
    "max_sell_per_transaction": 64,
    "enable_buy": true,
    "enable_sell": true
  },
  "prices": {
    "diamond": {
      "buy": 1000,
      "sell": 800,
      "name": "Diamond"
    }
  }
}
```

### Settings Description

- **currency**: Currency symbol (e.g., `$`, `₺`, `€`)
- **max_buy_per_transaction**: Maximum purchase amount per transaction
- **max_sell_per_transaction**: Maximum sell amount per transaction
- **enable_buy**: Enable/disable buy transactions
- **enable_sell**: Enable/disable sell transactions

### Adding Prices

You can add item prices to the `prices` section:

```json
"prices": {
  "item_id": {
    "buy": 100,
    "sell": 80,
    "name": "Item Display Name"
  }
}
```

**Note**: Item IDs must be written in lowercase (e.g., `diamond`, `iron_ingot`, `stone`).

## 🛠️ Development

### Project Structure

```
Kronshop/
├── src/
│   └── Market/
│       ├── Commands/          # Command classes
│       ├── Forms/             # Form interfaces
│       ├── FormAPI/           # Form API helpers
│       ├── LanguageManager.php
│       ├── Main.php           # Main plugin class
│       └── MarketManager.php   # Market management logic
├── resources/
│   └── config.json            # Default configuration
├── plugin.yml                  # Plugin definition file
└── README.md
```

### Building

To build the plugin:

```bash
# Install Composer dependencies (if any)
composer install

# Use PocketMine-MP's DevTools plugin to create a phar file
# or manually create a phar file
```

## 🐛 Known Issues

- Plugin is currently in Beta stage
- Some special items (containing NBT data) may not be supported

## 📝 Changelog

### v0.1-Beta
- Initial beta release
- Basic buy/sell features
- Kronnomy and BedrockEconomy support
- Form interfaces
- Category system
- Transaction lock protection

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork this repository
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the [MIT License](LICENSE).

## 👤 Author

**Kronnosy**

- GitHub: [@Kronnosy](https://github.com/Kronnosy)

## 🙏 Acknowledgments

- [PocketMine-MP](https://github.com/pmmp/PocketMine-MP) - Minecraft: Bedrock Edition server software
- [Kronnomy](https://github.com/Kronnosy/Kronnomy) - Economy plugin support
- [BedrockEconomy](https://github.com/cooldogepm/BedrockEconomy) - Alternative economy plugin support

## 📞 Support

For questions or issues:

- Create a new issue on the [Issues](https://github.com/Kronnosy/Kronshop/issues) page
- Join discussions on the [Discussions](https://github.com/Kronnosy/Kronshop/discussions) page

---

⭐ If you like this project, don't forget to give it a star!
