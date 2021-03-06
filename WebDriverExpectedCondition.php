<?php
// Copyright 2004-present Facebook. All Rights Reserved.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

/**
 * Canned ExpectedConditions which are generally useful within webdriver tests.
 *
 * @see WebDriverWait
 */
class WebDriverExpectedCondition {

  /**
   * A closure function to be executed by WebDriverWait. It should return
   * a truthy value, mostly boolean or a WebDriverElement, on success.
   */
  private $apply;

  public function getApply() {
    return $this->apply;
  }

  protected function __construct($apply) {
    $this->apply = $apply;
  }

  /**
   * An expectation for checking the title of a page.
   *
   * @param string title The expected title, which must be an exact match.
   * @return bool True when the title matches, false otherwise.
   */
  public static function titleIs($title) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($title) {
        return $title === $driver->getTitle();
      }
    );
  }

  /**
   * An expectation for checking that an element is present on the DOM of a
   * page. This does not necessarily mean that the element is visible.
   *
   * @param WebDriverBy $by The locator used to find the element.
   * @return WebDriverElement The element which is located.
   */
  public static function presenceOfElementLocated(WebDriverBy $by) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($by) {
        return $driver->findElement($by);
      }
    );
  }

  /**
   * An expectation for checking that an element is present on the DOM of a page
   * and visible. Visibility means that the element is not only displayed but
   * also has a height and width that is greater than 0.
   *
   * @param WebDriverBy $by The locator used to find the element.
   * @return WebDriverElement The element which is located and visible.
   */
  public static function visibilityOfElementLocated(WebDriverBy $by) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($by) {
        try {
          $element = $driver->findElement($by);
          return $element->isDisplayed() ? $element : null;
        } catch (ObsoleteElementWebDriverError $e) {
          return null;
        }
      }
    );
  }

  /**
   * An expectation for checking that an element, known to be present on the DOM
   * of a page, is visible. Visibility means that the element is not only
   * displayed but also has a height and width that is greater than 0.
   *
   * @param WebDriverElement $element The element to be checked.
   * @return WebDriverElement The same WebDriverElement once it is visible.
   */
  public static function visibilityOf(WebDriverElement $element) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($element) {
        return $element->isDisplayed() ? $element : null;
      }
    );
  }

  /**
   * An expectation for checking that there is at least one element present on a
   * web page.
   *
   * @param WebDriverBy $by The locator used to find the element.
   * @return array An array of WebDriverElements once they are located.
   */
  public static function presenceOfAllElementsLocatedBy(WebDriverBy $by) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($by) {
        $elements = $driver->findElements($by);
        return count($elements) > 0 ? $elements : null;
      }
    );
  }

  /**
   * An expectation for checking if the given text is present in the specified
   * element.
   *
   * @param WebDriverBy $by The locator used to find the element.
   * @param string $text The text to be presented in the element.
   * @return bool Whether the text is presented.
   */
  public static function textToBePresentInElement(
      WebDriverBy $by, string $text) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($by, $text) {
        try {
          $element_text = $driver->findElement($by)->getText();
          return strpos($element_text, $text) !== false;
        } catch (ObsoleteElementWebDriverError $e) {
          return null;
        }
      }
    );
  }

  /**
   * An expectation for checking if the given text is present in the specified
   * elements value attribute.
   *
   * @param WebDriverBy $by The locator used to find the element.
   * @param string $text The text to be presented in the element value.
   * @return bool Whether the text is presented.
   */
  public static function textToBePresentInElementValue(
      WebDriverBy $by, string $text) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($by, $text) {
        try {
          $element_text = $driver->findElement($by)->getAttribute('value');
          return strpos($element_text, $text) !== false;
        } catch (ObsoleteElementWebDriverError $e) {
          return null;
        }
      }
    );
  }

  /**
   * An expectation for checking that an element is either invisible or not
   * present on the DOM.
   *
   * @param WebDriverBy $by The locator used to find the element.
   * @return bool Whether there is no element located.
   */
  public static function invisibilityOfElementLocated(WebDriverBy $by) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($by) {
        try {
          return !($driver->findElement($by)->isDisplayed());
        } catch (NoSuchElementWebDriverError $e) {
          return true;
        } catch (ObsoleteElementWebDriverError $e) {
          return true;
        }
      }
    );
  }

  /**
   * An expectation for checking that an element with text is either invisible
   * or not present on the DOM.
   *
   * @param WebdriverBy $by The locator used to find the element.
   * @param string $text The text of the element.
   * @return bool Whether the text is found in the element located.
   */
  public static function invisibilityOfElementWithText(
      WebDriverBy $by, string $text) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($by, $text) {
        try {
          return !($driver->findElement($by)->getText() === $text);
        } catch (NoSuchElementWebDriverError $e) {
          return true;
        } catch (ObsoleteElementWebDriverError $e) {
          return true;
        }
      }
    );
  }

  /**
   * An expectation for checking an element is visible and enabled such that you
   * can click it.
   *
   * @param WebDriverBy $by The locator used to find the element
   * @return WebDriverElement The WebDriverElement once it is located, visible
   *                          and clickable
   */
  public static function elementToBeClickable(WebDriverBy $by) {
    $visibility_of_element_located =
      WebDriverExpectedCondition::visibilityOfElementLocated($by);
    return new WebDriverExpectedCondition(
      function ($driver) use ($visibility_of_element_located) {
        $element = call_user_func(
          $visibility_of_element_located->getApply(),
          $driver
        );
        try {
          if ($element !== null && $element->isEnabled()) {
            return $element;
          } else {
            return null;
          }
        } catch (ObsoleteElementWebDriverError $e) {
          return null;
        }
      }
    );
  }

  /**
   * Wait until an element is no longer attached to the DOM.
   *
   * @param WebDriverElement $element The element to wait for.
   * @return bool false if the element is still attached to the DOM, true
   *              otherwise.
   */
  public static function stalenessOf(WebDriverElement $element) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($element) {
        try {
          $element->isEnabled();
          return false;
        } catch (ObsoleteElementWebDriverError $e) {
          return true;
        }
      }
    );
  }

  /**
   * Wrapper for a condition, which allows for elements to update by redrawing.
   *
   * This works around the problem of conditions which have two parts: find an
   * element and then check for some condition on it. For these conditions it is
   * possible that an element is located and then subsequently it is redrawn on
   * the client. When this happens a ObsoleteElementWebDriverError is thrown
   * when the second part of the condition is checked.
   *
   * @param WebDriverExpectedCondition $condition The condition wrapped.
   * @return mixed The return value of the getApply() of the given condition.
   */
  public static function refreshed(WebDriverExpectedCondition $condition) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($condition) {
        try {
          return call_user_func($condition->getApply(), $driver);
        } catch (ObsoleteElementWebDriverError $e) {
          return null;
        }
      }
    );
  }

  /**
   * An expectation for checking if the given element is selected.
   *
   * @param mixed element_or_by Either the element or the locator.
   * @return bool whether the element is selected.
   */
  public static function elementToBeSelected($element_or_by) {
    return WebDriverExpectedCondition::elementSelectionStateToBe(
      $element_or_by,
      true
    );
  }

  /**
   * An expectation for checking if the given element is selected.
   *
   * @param mixed $element_or_by Either the element or the locator.
   * @param bool $selected The required state.
   * @return bool Whether the element is selected.
   */
  public static function elementSelectionStateToBe(
      $element_or_by, bool $selected) {
    if ($element_or_by instanceof WebDriverElement) {
      return new WebDriverExpectedCondition(
        function ($driver) use ($element_or_by, $selected) {
          return $element_or_by->isSelected === $selected;
        }
      );
    } else if ($element_or_by instanceof WebDriverBy) {
      return new WebDriverExpectedCondition(
        function ($driver) use ($element_or_by, $selected) {
          try {
            $element = $driver->findElement($element_or_by);
            return $element->isSelected === $selected;
          } catch (ObsoleteElementWebDriverError $e) {
            return null;
          }
        }
      );
    }
  }

  /**
   * An expectation with the logical opposite condition of the given condition.
   *
   * @param WebDriverExpectedCondition $condition The condition to be negated.
   * @return mixed The nagation of the result of the given condition.
   */
  public static function not(WebDriverExpectedCondition $condition) {
    return new WebDriverExpectedCondition(
      function ($driver) use ($condition) {
        $result = call_user_func($condition->getApply(), $driver);
        return !$result;
      }
    );
  }
}
