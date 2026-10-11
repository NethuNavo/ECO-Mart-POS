-- Run once against an existing ECO Mart POS database to normalize stored product image names.
-- Product images are served from asset/images/products/.
UPDATE `products`
SET `image` = CASE LOWER(SUBSTRING_INDEX(REPLACE(`image`, '\\', '/'), '/', -1))
    WHEN 'rice.jpg' THEN 'samba.png'
    WHEN 'sugar.jpg' THEN 'sugar.png'
    WHEN 'dhal.jpg' THEN 'dhal.png'
    WHEN 'anchor.jpg' THEN 'anchor.png'
    WHEN 'tea.jpg' THEN 'dilmha.png'
    WHEN 'coffee.jpg' THEN 'nescafe.png'
    WHEN 'egb.jpg' THEN 'gingerbeer.png'
    WHEN 'cracker.jpg' THEN 'creamcaker.png'
    WHEN 'lemonpuff.jpg' THEN 'lemonpuff.png'
    WHEN 'milk.jpg' THEN 'milk.png'
    WHEN 'cheese.jpg' THEN 'cheese.png'
    WHEN 'astra.jpg' THEN 'astra.png'
    WHEN 'eggs.jpg' THEN 'eggs.png'
    WHEN 'carrots.jpg' THEN 'carrorts.png'
    WHEN 'onions.jpg' THEN 'onion.png'
    WHEN 'bananas.jpg' THEN 'bananas.png'
    WHEN 'chicken.jpg' THEN 'chicken.png'
    WHEN 'sausages.jpg' THEN 'default_product.svg'
    WHEN 'sunlight.jpg' THEN 'soap.png'
    WHEN 'surfexcel.jpg' THEN 'surfexcel.png'
    WHEN 'vim.jpg' THEN 'vim.png'
    WHEN 'signal.jpg' THEN 'signal.png'
    WHEN 'lifebuoy.jpg' THEN 'lifeboy.png'
    WHEN 'icecream.jpg' THEN 'icecream.png'
    WHEN 'flour.jpg' THEN 'default_product.svg'
    WHEN 'default_product.png' THEN 'default_product.svg'
    ELSE `image`
END
WHERE LOWER(SUBSTRING_INDEX(REPLACE(`image`, '\\', '/'), '/', -1)) IN (
    'rice.jpg', 'sugar.jpg', 'dhal.jpg', 'anchor.jpg', 'tea.jpg', 'coffee.jpg',
    'egb.jpg', 'cracker.jpg', 'lemonpuff.jpg', 'milk.jpg', 'cheese.jpg', 'astra.jpg',
    'eggs.jpg', 'carrots.jpg', 'onions.jpg', 'bananas.jpg', 'chicken.jpg', 'sausages.jpg',
    'sunlight.jpg', 'surfexcel.jpg', 'vim.jpg', 'signal.jpg', 'lifebuoy.jpg', 'icecream.jpg',
    'flour.jpg', 'default_product.png'
);
