<?php
declare(strict_types = 1);

require 'Model/Customer.php';

require 'Model/Product.php';

require 'Model/CustomerGroup.php';

require 'Model/Dataloader.php';

class HomepageController
{
    //render function with both $_GET and $_POST vars available if it would be needed.
    public function render(/*array $GET, array $POST*/)
    {
        $userArray = [];
        $productArray = [];
        $groupArray = [];

        $loader = new Dataloader();

        $customerData = $loader->fetchUserData('data/customers.json');
        $groupData = $loader->fetchUserData('data/groups.json');
        $productData = $loader->fetchUserData('data/products.json');

        foreach ($customerData as $user) {
            array_push($userArray, new Customer($user['name'], $user['id'], $user['group_id']));
        }
        foreach ($productData as $item) {
            array_push($productArray, new Product($item['name'], $item['id'], $item['price'], $item['description']));
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_POST["customers"]) || !isset($_POST["products"])) {
                $_POST["customers"] = $_POST["customers"][0];
                $_POST["products"] = $_POST["products"][0];
            }
            else {
                $customerPost = $_POST["customers"];
                $productPost = $_POST["products"];

                //var_dump($userArray["$customerPost"]->getGroupId());

                $groupID = $userArray["$customerPost"]->getGroupId();

                // groupId in this case refers to the group ID, which we know from user input (group id is linked).
                // groupsArray refers to the associative array which we converted from groups.json (we named it $groupData some previous lines)
                function findGroup ($groupId, $groupsArray)
                {
                    foreach ($groupsArray as $group) {
                        if ($group['id'] == $groupId) {
                            return $group;
                        }
                    }
                }

                // Using the findGroup function which returns a single group, which the user belongs to
                // we find other groups, which are linked together.
                while ($groupID !== null)
                {
                    $groupsChain = findGroup($groupID, $groupData);

                    array_push($groupArray,$groupsChain);
                    if (isset($groupsChain['group_id']))
                    {
                        // If the current group over which we are looping has a group ID
                        // we override the groupID variable with the new groupID of the former group.
                        $groupID = $groupsChain['group_id'];
                    }
                    else
                    {
                        $groupID = null;
                    }
                }

                var_dump($groupArray);

                //var_dump($groupArray);

                $arrayFixedDiscount = $userArray["$customerPost"]->discountSorter($groupArray, 'fixed_discount');
                //var_dump($arrayFixedDiscount);
                $arrayVariableDiscount = $userArray["$customerPost"]->discountSorter($groupArray, 'variable_discount');
                //var_dump($arrayVariableDiscount);

                var_dump($productArray["$productPost"]->getPrice());
                $totalDiscount = $productArray["$productPost"]->totalDiscount($arrayFixedDiscount);
                var_dump($totalDiscount);
                $totalPrice = $productArray["$productPost"]->discountChecker($totalDiscount, $productArray["$productPost"]->getPrice());
                var_dump($totalPrice);

                $highestVariableDiscount = 0;
                if (is_array($productArray))
                {
                    $highestVariableDiscount = $productArray["$productPost"]->highestVariableDiscount($arrayVariableDiscount);
                }

                var_dump($highestVariableDiscount);

                $totalVariableDiscount = $productArray["$productPost"]->variableDiscountFixedAmount($highestVariableDiscount, $totalPrice);
                var_dump($totalVariableDiscount);

                $newPrice = $productArray["$productPost"]->variableDiscountCalculator($totalVariableDiscount, $totalPrice);
                var_dump($newPrice);




            }





















        }

















        //you should not echo anything inside your controller - only assign vars here
        // then the view will actually display them.

        //load the view
        require 'View/homepage.php';

        function whatIsHappening()
        {
            echo '<h2>$_GET</h2>';
            var_dump($_GET);
            echo '<h2>$_POST</h2>';
            var_dump($_POST);
            echo '<h2>$_COOKIE</h2>';
            var_dump($_COOKIE);
            echo '<h2>$_SESSION</h2>';
            var_dump($_SESSION);
        }
        whatIsHappening();

        /* if (!isset($_POST['customer'])){
             return;
         }
         else {
             $_POST['customer'] =
         }*/

    }
}
