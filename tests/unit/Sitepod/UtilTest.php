<?php
/* This file is part of Sitepod.
 *
 * Sitepod is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Sitepod is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Sitepod.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Sitepod;

use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{

    function testStringToVariableName()
    {
        $this->assertEquals('', Util::stringToVariableName(''));
        $this->assertEquals('something', Util::stringToVariableName('something'));
        $this->assertEquals('something with \"', Util::stringToVariableName('something with "'));
        $this->assertEquals("something with \'", Util::stringToVariableName("something with '"));
        $this->assertEquals('something with \"\"', Util::stringToVariableName('something with ""'));
        $this->assertEquals("something with \'\'", Util::stringToVariableName("something with ''"));
    }

    function testVariableNameToString()
    {
        $this->assertEquals('', Util::variableNameToString(''));
        $this->assertEquals('something', Util::variableNameToString('something'));

        /**
         * @FIXME: I guess there are some unexpected behaviours here. Could you explain what is the expected behaviour?
         * @FIXME: The name of method (variableNameToString()) suggests it is the reverse of the stringToVariableName() method, but it is not.
         */
        $this->assertEquals('something with \"', Util::variableNameToString('something with \"'));
        $this->assertEquals("something with \\", Util::variableNameToString("something with \'"));
        $this->assertEquals('something with \"\"', Util::variableNameToString('something with \"\"'));
        $this->assertEquals("something with \'\\", Util::variableNameToString("something with \'\'"));
        $this->assertEquals("something with \'\\", Util::variableNameToString("'something with \'\'"));
    }

    function testToArray()
    {
        $this->assertCount(1, Util::toArray('', ':'));
        $this->assertCount(2, Util::toArray('foo:bar', ':'));
    }

    function testToArrayShouldTrimElements()
    {
        $fooBar = Util::toArray('foo  :  bar', ':');
        $this->assertCount(2, $fooBar);
        $this->assertEquals('foo', $fooBar[0]);
        $this->assertEquals('bar', $fooBar[1]);
    }

    function testArrToStringReadable()
    {
        $this->assertEquals('', Util::arrToStringReadable([], ','));
        $this->assertEquals('foo: bar', Util::arrToStringReadable(['foo' => 'bar'], ','));
        /** @FIXME: These should be more readable */
        /** @FIXME: Need a space after the delimiter */
        $this->assertEquals('foo: bar,foo2: baz', Util::arrToStringReadable(['foo' => 'bar', 'foo2' => 'baz'], ','));
        $this->assertEquals('foo: bar: baz', Util::arrToStringReadable(['foo' => ['bar' => 'baz']], ','));

        /** @FIXME: In the second one I can't understand that 'foo2: bar' inside 'foo' */
        $this->assertEquals('foo: bar: baz,foo2: bar2', Util::arrToStringReadable(['foo' => ['bar' => 'baz'], 'foo2' => 'bar2'], ','));
        $this->assertEquals('foo: bar: baz,foo2: bar2,foo3: bar3', Util::arrToStringReadable(['foo' => ['bar' => 'baz', 'foo2' => 'bar2'], 'foo3' => 'bar3'], ','));
        /** @FIXME: I suppose print_r() or var_dump() kind of thing would be better. Or simple use the short array format inside the string. */
    }

    function testArrToString()
    {
        $this->assertEquals('', Util::arrToString([], ','));
        $this->assertEquals('foo', Util::arrToString(['foo'], ','));
        $this->assertEquals('foo,bar', Util::arrToString(['foo','bar'], ','));
        /** @FIXME: Doesn't work with associative array or multi dimension array */
    }
}
