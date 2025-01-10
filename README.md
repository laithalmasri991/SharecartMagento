
# Magento 2 Share Cart Module

The **Magento 2 Share Cart** module allows users to share their shopping cart with others via a unique link. It’s a convenient way to collaborate, plan purchases, or share products with friends or colleagues.

---

## Features

- Generate unique shareable links for shopping carts.
- Restore shared cart items seamlessly for recipients.
- Configurable via the Magento admin panel.
- Compatible with PHP 7.4 and PHP 8.0+.

---

## Requirements

- Magento 2.4.x or later
- PHP 7.4 or 8.0+

---

## Installation

### Using Composer

1. Add the module to your project:
   ```bash
   composer require laith/magentosharecart
   ```

2. Enable the module:
   ```bash
   php bin/magento module:enable Laith_ShareCart
   ```

3. Run the Magento setup upgrade:
   ```bash
   php bin/magento setup:upgrade
   ```

4. If you are in production mode, deploy static content:
   ```bash
   php bin/magento setup:static-content:deploy
   ```

5. Clear the cache:
   ```bash
   php bin/magento cache:flush
   ```

---

## Usage

1. **Enable the Module**:
   Navigate to **Stores > Configuration > Share Cart Settings** in the Magento admin panel. Enable the module from this section.

2. **Generate a Shareable Link**:
   - Go to the cart page.
   - Click the "Share Cart" button. A unique link will be generated.

3. **Share the Link**:
   - Copy the generated link and share it via email, messaging apps, or social media.

4. **Restore the Cart**:
   - Recipients can click the shared link to restore the cart items automatically in their session.

---

## Configuration

The module can be configured under **Stores > Configuration > Share Cart Settings**:

- **Enable/Disable**: Toggle the module functionality.
- **Advanced Settings**: Configure additional options (if available).

---

## Contribution

We welcome contributions to improve the module! Here's how you can contribute:

1. Fork the repository.
2. Create a new branch for your feature or fix:
   ```bash
   git checkout -b feature-name
   ```
3. Make your changes and commit them:
   ```bash
   git commit -m "Add your feature or fix description"
   ```
4. Push to your branch:
   ```bash
   git push origin feature-name
   ```
5. Submit a pull request to the main repository.

---

## Support

If you encounter any issues or have questions about the module:

1. Open an [issue on GitHub](https://github.com/laithalmasri991/Magento-Sharecart/issues).
2. Contact us at **[laith.k.m4@gmail.com]** for direct support.

---

## License

This module is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## Author

Developed by **Laith Almasri**  
[GitHub Profile](https://github.com/laithalmasri991)
