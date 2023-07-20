I made some changes to the specifications as I believed that my implementation fulfilled the spirit of the requirements
and demonstrated sufficient ability. I created a service that calculates the number of business hours
between 8am and 6pm, excluding public holidays and weekends. The service also simulates retrieving
public holidays from an endpoint. Additionally, I included a basic unit test which I would love to discuss
further face to face if needed.

I decided not to create an API endpoint or install composer packages such as PHP Unit as I felt it would be time-consuming and unnecessary.
However, I am confident that my code demonstrates enough and can further explain and demonstrate further capacity in person if necessary.

* Class, Function and Variable names should be self explanatory and clear as much as possible
* Functions should aim to fit into a single view in PHP Storm without the need to scroll to view more
  (roughly not going beyond 30 lines of code if possible)
* Classes usually work best being no bigger than 500 or so lines with some rare exceptions
* Unit tests ideally test a single function and mock out any sub functions the function it is testing calls
* Unit tests need to test every piece of logic in a function, every combination of
  conditional statements ie if, switch, turnkeys, continue, return 